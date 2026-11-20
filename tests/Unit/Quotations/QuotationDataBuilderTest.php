<?php

namespace Tests\Unit\Quotations;

use App\Services\Quotations\DetailedQuotationDataBuilder;
use App\Services\Quotations\ItineraryQuotationDataBuilder;
use App\Services\Quotations\QuotationDataBuilder;
use App\Services\Quotations\QuotationPresentationAssembler;
use Tests\TestCase;

class QuotationDataBuilderTest extends TestCase
{
    private function makeTour(array $attrs = []): object
    {
        $defaults = [
            'tour_id' => 101,
            'display_id' => 'DMC-ORD1234',
            'adult' => 4,
            'child' => 1,
            'infant' => 0,
            'foc_size' => 0,
            'tour_type' => 'FIT',
            'is_pro' => 0,
            'discount' => 0,
            'discount_amount' => 0,
            'currency' => 'SGD',
            'city' => '',
            'destination' => 'Singapore',
            'dmc_id' => null,
            'created_by' => null,
            'check_in_time' => '2026-04-01',
            'check_out_time' => '2026-04-05',
            'taxes' => [],
            'currency_markups' => [],
        ];

        $merged = array_merge($defaults, $attrs);

        return new class($merged)
        {
            /** @var array<string, mixed> */
            private array $attributes;

            public function __construct(array $attributes)
            {
                $this->attributes = $attributes;
                foreach ($attributes as $key => $value) {
                    $this->{$key} = $value;
                }
            }

            /**
             * @return array<string, mixed>
             */
            public function getAttributes(): array
            {
                return $this->attributes;
            }
        };
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function makePayload(array $overrides = []): array
    {
        $tour = $overrides['tour'] ?? $this->makeTour();

        return array_merge([
            'tour' => $tour,
            'orders' => collect(),
            'bookingDetails' => [
                'booking_id' => $tour->display_id,
                'lead_guest_name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '+65 111',
                'no_of_adults' => $tour->adult,
                'no_of_children' => $tour->child,
                'no_of_infants' => $tour->infant,
            ],
            'travelDetails' => [
                'destination' => $tour->destination,
                'travel_date_from' => 'Wednesday- 01/04/2026',
                'travel_date_to' => 'Sunday- 05/04/2026',
                'duration' => '4 Days',
            ],
            'tourPrices' => [
                'single_sharing' => 0,
                'double_sharing' => 0,
                'triple_sharing' => 0,
                'other_services_single' => 0,
                'other_services_double' => 0,
                'hotel_price_options' => [],
                'country_sharing' => [],
                'discounts' => [],
                'discount_total' => 0,
                'supplements' => [],
            ],
            'hotelOptions' => [],
            'servicesByType' => [],
            'servicesByDate' => [],
            'countryQuotationGroups' => [],
            'baseCurrency' => 'SGD',
            'selectedCurrency' => 'SGD',
            'exchangeRate' => 1.0,
            'logoType' => 'dmc',
            'user_agency' => null,
            'dmcUser' => null,
            'dmcDetails' => [
                'company_name' => 'Test DMC',
                'address' => '1 Test Street',
                'email' => 'dmc@example.com',
                'phone' => '+65 000',
            ],
            'dmcCompanyName' => 'Test DMC',
            'agentDetails' => [],
            'proposalDetails' => [
                'proposal_date' => '01 Apr 2026',
                'proposal_validity' => 'N/A',
            ],
            'bankDetails' => [],
            'termsAndConditions' => '',
            'paymentTerms' => [],
            'quotationInformationHtml' => null,
        ], $overrides);
    }

    public function test_group_foc_subtracts_from_displayed_adults(): void
    {
        $tour = $this->makeTour([
            'tour_type' => 'GROUP',
            'adult' => 12,
            'foc_size' => 2,
            'child' => 0,
        ]);
        $payload = $this->makePayload(['tour' => $tour]);
        $payload['bookingDetails']['no_of_adults'] = 12;

        $view = (new QuotationPresentationAssembler)->assemble($payload, 'itinerary');

        $this->assertSame(10, $view['displayAdults']);
        $this->assertSame('10A', $view['paxText']);
        $this->assertTrue($view['hasFoc']);
        $this->assertSame(2, $view['focSize']);
        $this->assertSame(12, $view['totalPax']);
    }

    public function test_fit_does_not_subtract_foc_from_displayed_adults(): void
    {
        $tour = $this->makeTour([
            'tour_type' => 'FIT',
            'adult' => 4,
            'foc_size' => 1,
        ]);
        $payload = $this->makePayload(['tour' => $tour]);
        $payload['bookingDetails']['no_of_adults'] = 4;

        $view = (new QuotationPresentationAssembler)->assemble($payload, 'detailed');

        $this->assertSame(4, $view['displayAdults']);
        $this->assertSame('04A 01C', $view['paxText']);
    }

    public function test_inr_currency_label_and_amount_ceil_match_existing_rules(): void
    {
        $payload = $this->makePayload([
            'selectedCurrency' => 'INR',
            'baseCurrency' => 'INR',
            'exchangeRate' => 1.0,
        ]);

        $view = (new QuotationPresentationAssembler)->assemble($payload, 'itinerary');

        $this->assertSame('INR', $view['currencyLabel']);
        $this->assertSame('2', ($view['formatAmount'])(1.1));
        $this->assertSame('0', ($view['formatAmount'])(0));
        $this->assertSame('INR 2', ($view['formatMoney'])(1.1));
        $this->assertSame('ORD1234', $view['formattedDisplayId']);
    }

    public function test_builders_set_quotation_type_and_normalized_contract(): void
    {
        $payload = $this->makePayload();

        $itinerary = app(ItineraryQuotationDataBuilder::class)->build($payload);
        $detailed = app(DetailedQuotationDataBuilder::class)->build($payload);
        $merged = app(QuotationDataBuilder::class)->forViewName($payload, 'single-tour-package.detailedqutation');

        $this->assertSame('itinerary', $itinerary['quotationType']);
        $this->assertSame('detailed', $detailed['quotationType']);
        $this->assertSame('detailed', $merged['quotationData']['quotation']['type']);
        $this->assertSame('Jane Doe', $merged['quotationData']['booking']['lead_guest_name']);
        $this->assertSame('Test DMC', $merged['quotationData']['branding']['company_name']);
        $this->assertSame('dmc', $merged['quotationData']['branding']['mode']);
        $this->assertArrayHasKey('formattedDisplayId', $merged);
        $this->assertArrayHasKey('overallPackageRows', $merged);
        $this->assertArrayHasKey('countrySharingRows', $merged);
        $this->assertSame(0.0, (float) $merged['totalCostAmount']);
        $this->assertSame([], $merged['allCountryKeys']);
    }

    public function test_helper_view_name_maps_itinerary_and_detailed(): void
    {
        $builder = app(QuotationDataBuilder::class);
        $payload = $this->makePayload();

        $itinerary = $builder->forViewName($payload, 'single-tour-package.quotation');
        $detailed = $builder->forViewName($payload, 'single-tour-package.detailedqutation');

        $this->assertSame('itinerary', $itinerary['quotationType']);
        $this->assertSame('detailed', $detailed['quotationType']);
    }

    public function test_itinerary_and_detailed_blades_render_with_prepared_data(): void
    {
        $payload = $this->makePayload();
        $builder = app(QuotationDataBuilder::class);

        $itineraryHtml = view(
            'single-tour-package.quotation',
            $builder->forViewName($payload, 'single-tour-package.quotation')
        )->render();
        $detailedHtml = view(
            'single-tour-package.detailedqutation',
            $builder->forViewName($payload, 'single-tour-package.detailedqutation')
        )->render();

        $this->assertStringContainsString('LEAD GUEST NAME', $itineraryHtml);
        $this->assertStringContainsString('Jane Doe', $itineraryHtml);
        $this->assertStringContainsString('TOTAL COST', $itineraryHtml);
        $this->assertStringContainsString('Acco + Service Quotation', $itineraryHtml);

        $this->assertStringContainsString('LEAD GUEST NAME', $detailedHtml);
        $this->assertStringContainsString('Jane Doe', $detailedHtml);
        $this->assertStringContainsString('TOTAL COST', $detailedHtml);
        $this->assertStringContainsString('Packaged Quotation', $detailedHtml);
    }

    public function test_normalized_pricing_uses_booked_occupancy_not_double_as_generic_total(): void
    {
        $payload = $this->makePricedPayload([
            'tourPrices' => [
                'single_sharing' => 170,
                'double_sharing' => 120,
                'triple_sharing' => 0,
                'other_services_single' => 20,
                'other_services_double' => 20,
            ],
            'hotel_price_options' => [[
                'hotel_name' => 'Marina Bay',
                'country' => 'Singapore',
                'city' => 'Singapore',
                'currency' => 'SGD',
                'selected_persons' => 1,
                'number_of_rooms' => 1,
            ]],
        ]);

        $merged = app(QuotationDataBuilder::class)->forViewName($payload, 'single-tour-package.quotation');
        $pricing = $merged['quotationData']['pricing'];

        $this->assertArrayNotHasKey('hotel_total', $pricing);
        $this->assertSame('single', $pricing['occupancy_key']);
        $this->assertSame(150.0, (float) $pricing['hotel_only_single_total']);
        $this->assertSame(100.0, (float) $pricing['hotel_only_double_total']);
        $this->assertNull($pricing['hotel_only_triple_total']);
        $this->assertSame(150.0, (float) $pricing['hotel_total_booked_occupancy']);
        $this->assertSame(20.0, (float) $pricing['other_services_total_booked_occupancy']);
        $this->assertSame('SGD', $pricing['display_currency']);
        $this->assertNull($pricing['country_sharing_combined']);
    }

    public function test_booked_triple_occupancy_is_null_when_triple_hotel_only_is_unavailable(): void
    {
        $payload = $this->makePricedPayload([
            'tourPrices' => [
                'single_sharing' => 170,
                'double_sharing' => 120,
                'triple_sharing' => 0,
                'other_services_single' => 20,
                'other_services_double' => 20,
            ],
            'hotel_price_options' => [[
                'hotel_name' => 'Marina Bay',
                'country' => 'Singapore',
                'city' => 'Singapore',
                'currency' => 'SGD',
                'selected_persons' => 3,
                'number_of_rooms' => 1,
            ]],
        ]);

        $pricing = app(QuotationDataBuilder::class)
            ->forViewName($payload, 'single-tour-package.quotation')['quotationData']['pricing'];

        $this->assertSame('triple', $pricing['occupancy_key']);
        $this->assertNull($pricing['hotel_only_triple_total']);
        $this->assertNull($pricing['hotel_total_booked_occupancy']);
    }

    public function test_packaged_share_rows_combine_hotel_other_and_child(): void
    {
        $payload = $this->makePricedPayload();
        $detailed = app(DetailedQuotationDataBuilder::class)->build($payload);
        $itinerary = app(ItineraryQuotationDataBuilder::class)->build($payload);

        $packaged = $detailed['countrySharingRows'][0];
        $split = $itinerary['countrySharingRows'][0];

        $this->assertSame(170.0, $packaged['combined_single']);
        $this->assertSame(120.0, $packaged['combined_double']);
        $this->assertSame(0.0, $packaged['combined_triple']);
        $this->assertSame(55.0, $packaged['child_all']);
        $this->assertSame(2, $packaged['booked_occupancy_columns']);
        $this->assertStringContainsString('SGD 120(Adult)', $packaged['cell_double']);
        $this->assertStringContainsString('SGD 55(Child)', $packaged['cell_double']);
        $this->assertSame('--', $packaged['cell_triple']);
        $this->assertArrayNotHasKey('other_cell_html', $packaged);

        $this->assertArrayNotHasKey('combined_single', $split);
        $this->assertSame(40.0, $split['child_bed_total_resolved']);
        $this->assertStringContainsString('SGD 100(Adult)', $split['cell_double']);
        $this->assertStringContainsString('SGD 40(Child)', $split['cell_double']);
        $this->assertStringContainsString('SGD 20(Adult)', $split['other_cell_html']);
        $this->assertStringContainsString('SGD 15(Child)', $split['other_cell_html']);
        $this->assertSame('--', $split['cell_triple']);
    }

    public function test_pro_tour_copies_double_hotel_rate_onto_single(): void
    {
        $payload = $this->makePricedPayload([
            'tour' => $this->makeTour(['is_pro' => 1, 'adult' => 2, 'child' => 0]),
        ]);
        $payload['bookingDetails']['no_of_adults'] = 2;
        $payload['bookingDetails']['no_of_children'] = 0;

        $itinerary = app(ItineraryQuotationDataBuilder::class)->build($payload);
        $detailed = app(DetailedQuotationDataBuilder::class)->build($payload);

        $this->assertTrue($itinerary['isProTour']);
        $this->assertSame(100.0, (float) $itinerary['hotelOnlySingleTotal']);
        $this->assertSame(100.0, (float) $itinerary['hotelOnlyDoubleTotal']);
        $this->assertStringContainsString('SGD 100(Adult)', $itinerary['countrySharingRows'][0]['cell_single']);
        $this->assertSame(120.0, $detailed['countrySharingRows'][0]['combined_single']);
        $this->assertSame(120.0, $detailed['countrySharingRows'][0]['combined_double']);
    }

    public function test_child_with_bed_is_resolved_from_share_then_country_totals(): void
    {
        $payload = $this->makePricedPayload([
            'country_sharing' => [[
                'country' => 'Singapore',
                'city' => 'Singapore',
                'currency' => 'SGD',
                'hotel_single' => 150,
                'hotel_double' => 100,
                'hotel_triple' => 0,
                'other_services_single' => 20,
                'other_services_double' => 20,
                'other_services_child' => 0,
            ]],
            'hotel_price_options' => [[
                'hotel_name' => 'Marina Bay',
                'country' => 'Singapore',
                'city' => 'Singapore',
                'currency' => 'SGD',
                'selected_persons' => 2,
                'number_of_rooms' => 1,
                'children_with_bed' => 1,
                'child_with_bed_total' => 40,
            ]],
        ]);

        $itinerary = app(ItineraryQuotationDataBuilder::class)->build($payload);

        $this->assertSame(40.0, $itinerary['countrySharingRows'][0]['child_bed_total_resolved']);
        $this->assertStringContainsString('SGD 40(Child)', $itinerary['countrySharingRows'][0]['cell_double']);
    }

    public function test_hotel_and_other_services_feed_package_subtotal(): void
    {
        $payload = $this->makePricedPayload(array_merge($this->adultOnlySharing(), [
            'tour' => $this->makeTour(['adult' => 2, 'child' => 0]),
        ]));
        $payload['bookingDetails']['no_of_adults'] = 2;
        $payload['bookingDetails']['no_of_children'] = 0;

        $view = (new QuotationPresentationAssembler)->assemble($payload, 'itinerary');

        $this->assertSame(240.0, (float) $view['overallLinesSubtotal']);
        $this->assertSame(240.0, (float) $view['totalCostAmount']);
        $kinds = array_column($view['overallPackageRows'][0]['services'] ?? [], 'service');
        $this->assertContains('Hotel-Accommodation', $kinds);
        $this->assertContains('Other Service(s)', $kinds);
    }

    public function test_markup_discount_rows_and_percentage_and_fixed_fallbacks(): void
    {
        $rowDiscount = $this->makePricedPayload(array_merge($this->adultOnlySharing(), [
            'tour' => $this->makeTour(['adult' => 2, 'child' => 0]),
            'tourPrices' => [
                'discounts' => [['amount' => 15, 'currency' => 'SGD']],
                'discount_total' => 15,
            ],
        ]));
        $rowDiscount['bookingDetails']['no_of_adults'] = 2;
        $rowDiscount['bookingDetails']['no_of_children'] = 0;

        $rowView = (new QuotationPresentationAssembler)->assemble($rowDiscount, 'itinerary');
        $this->assertSame(15.0, (float) $rowView['overallDiscountShown']);
        $this->assertSame(225.0, (float) $rowView['totalCostAmount']);

        $percent = $this->makePricedPayload(array_merge($this->adultOnlySharing(), [
            'tour' => $this->makeTour([
                'adult' => 2,
                'child' => 0,
                'currency_markups' => [[
                    'country' => 'Singapore',
                    'currency' => 'SGD',
                    'discount_type' => 'percentage',
                    'discount_value' => 10,
                ]],
            ]),
        ]));
        $percent['bookingDetails']['no_of_adults'] = 2;
        $percent['bookingDetails']['no_of_children'] = 0;
        $percentView = (new QuotationPresentationAssembler)->assemble($percent, 'itinerary');
        $this->assertSame(24.0, (float) $percentView['overallDiscountShown']);
        $this->assertSame(216.0, (float) $percentView['totalCostAmount']);

        $fixed = $this->makePricedPayload(array_merge($this->adultOnlySharing(), [
            'tour' => $this->makeTour([
                'adult' => 2,
                'child' => 0,
                'currency_markups' => [[
                    'country' => 'Singapore',
                    'currency' => 'SGD',
                    'discount_type' => 'flat',
                    'discount_value' => 25,
                ]],
            ]),
        ]));
        $fixed['bookingDetails']['no_of_adults'] = 2;
        $fixed['bookingDetails']['no_of_children'] = 0;
        $fixedView = (new QuotationPresentationAssembler)->assemble($fixed, 'itinerary');
        $this->assertSame(25.0, (float) $fixedView['overallDiscountShown']);
        $this->assertSame(215.0, (float) $fixedView['totalCostAmount']);
    }

    public function test_percentage_tax_applies_after_discount(): void
    {
        $payload = $this->makePricedPayload(array_merge($this->adultOnlySharing(), [
            'tour' => $this->makeTour([
                'adult' => 2,
                'child' => 0,
                'taxes' => [[
                    'tax_name' => 'GST',
                    'tax_type' => 'percentage',
                    'tax_value' => 10,
                    'calculate_on' => 'total',
                ]],
            ]),
        ]));
        $payload['bookingDetails']['no_of_adults'] = 2;
        $payload['bookingDetails']['no_of_children'] = 0;

        $view = (new QuotationPresentationAssembler)->assemble($payload, 'itinerary');

        $this->assertSame(240.0, (float) $view['overallLinesSubtotal']);
        $this->assertSame(24.0, (float) $view['overallTaxShown']);
        $this->assertSame(24.0, (float) $view['overallTaxBreakdown']['GST']);
        $this->assertSame(264.0, (float) $view['totalCostAmount']);
    }

    public function test_multiple_hotels_and_countries_are_grouped(): void
    {
        $payload = $this->makePricedPayload([
            'hotelOptions' => [
                [
                    'hotel_name' => 'Marina Bay',
                    'hotel_category' => 'Deluxe',
                    'country' => 'Singapore',
                    'city' => 'Singapore',
                    'currency' => 'SGD',
                ],
                [
                    'hotel_name' => 'Orchid Hotel',
                    'hotel_category' => 'Superior',
                    'country' => 'Singapore',
                    'city' => 'Singapore',
                    'currency' => 'SGD',
                ],
            ],
            'country_sharing' => [
                [
                    'country' => 'Singapore',
                    'city' => 'Singapore',
                    'currency' => 'SGD',
                    'hotel_single' => 150,
                    'hotel_double' => 100,
                    'hotel_triple' => 0,
                    'other_services_single' => 20,
                    'other_services_double' => 20,
                    'other_services_child' => 15,
                    'child_bed_total' => 40,
                ],
                [
                    'country' => 'Indonesia',
                    'city' => 'Batam',
                    'currency' => 'SGD',
                    'hotel_single' => 80,
                    'hotel_double' => 60,
                    'hotel_triple' => 0,
                    'other_services_single' => 10,
                    'other_services_double' => 10,
                    'other_services_child' => 0,
                    'child_bed_total' => 0,
                ],
            ],
        ]);

        $view = app(ItineraryQuotationDataBuilder::class)->build($payload);
        $countries = array_column($view['countrySharingRows'], 'country');
        $hotelNames = array_map(
            fn ($h) => $h['hotel_name'] ?? '',
            array_merge(...array_values($view['hotelsByCountry'] ?: [[]]))
        );

        $this->assertContains('Singapore', $countries);
        $this->assertContains('Indonesia', $countries);
        $this->assertContains('Marina Bay', $hotelNames);
        $this->assertContains('Orchid Hotel', $hotelNames);
        $this->assertCount(2, $view['countrySharingRows']);
    }

    public function test_return_stay_splitting_keeps_repeat_city_separate(): void
    {
        $payload = $this->makePricedPayload([
            'tour' => $this->makeTour([
                'adult' => 2,
                'child' => 0,
                'city' => 'Batam (Indonesia) [2026-09-23→2026-09-26], Singapore (Singapore) [2026-09-26→2026-09-29], Batam (Indonesia) [2026-09-29→2026-10-01]',
            ]),
            'hotel_price_options' => [
                [
                    'hotel_name' => 'Harmoni',
                    'country' => 'Indonesia',
                    'city' => 'Batam',
                    'currency' => 'SGD',
                    'selected_persons' => 2,
                    'number_of_rooms' => 1,
                    'date_range' => '23 Sep 2026 to 26 Sep 2026',
                    'double' => 60,
                ],
                [
                    'hotel_name' => 'Marina Bay',
                    'country' => 'Singapore',
                    'city' => 'Singapore',
                    'currency' => 'SGD',
                    'selected_persons' => 2,
                    'number_of_rooms' => 1,
                    'date_range' => '26 Sep 2026 to 29 Sep 2026',
                    'double' => 100,
                ],
                [
                    'hotel_name' => 'Harmoni Return',
                    'country' => 'Indonesia',
                    'city' => 'Batam',
                    'currency' => 'SGD',
                    'selected_persons' => 2,
                    'number_of_rooms' => 1,
                    'date_range' => '29 Sep 2026 to 01 Oct 2026',
                    'double' => 55,
                    'is_return' => true,
                ],
            ],
            'country_sharing' => [
                [
                    'country' => 'Indonesia',
                    'city' => 'Batam',
                    'currency' => 'SGD',
                    'hotel_double' => 60,
                    'other_services_single' => 0,
                    'other_services_double' => 0,
                ],
                [
                    'country' => 'Singapore',
                    'city' => 'Singapore',
                    'currency' => 'SGD',
                    'hotel_double' => 100,
                    'other_services_single' => 20,
                    'other_services_double' => 20,
                ],
            ],
        ]);
        $payload['bookingDetails']['no_of_adults'] = 2;
        $payload['bookingDetails']['no_of_children'] = 0;

        $view = app(ItineraryQuotationDataBuilder::class)->build($payload);
        $batamRows = array_values(array_filter(
            $view['countrySharingRows'],
            fn ($row) => ($row['country'] ?? '') === 'Indonesia'
        ));

        $this->assertGreaterThanOrEqual(2, count($batamRows));
        $this->assertTrue(collect($batamRows)->contains(fn ($row) => !empty($row['is_return'])));
        $this->assertTrue(collect($view['staySegments'])->contains(fn ($stay) => !empty($stay['is_return'])));
    }

    public function test_supplements_are_appended_to_package_rows(): void
    {
        $payload = $this->makePricedPayload([
            'tour' => $this->makeTour(['adult' => 2, 'child' => 0]),
            'tourPrices' => [
                'supplements' => [[
                    'type' => 'attraction',
                    'name' => 'Night Safari',
                    'single' => 30,
                    'double' => 30,
                    'currency' => 'SGD',
                ]],
            ],
        ]);
        $payload['bookingDetails']['no_of_adults'] = 2;
        $payload['bookingDetails']['no_of_children'] = 0;

        $view = (new QuotationPresentationAssembler)->assemble($payload, 'itinerary');
        $names = array_map(fn ($row) => (string) ($row['name'] ?? ''), $view['overallPackageRows']);

        $this->assertTrue(collect($names)->contains(fn ($name) => str_contains($name, 'Night Safari')));
        $supp = collect($view['overallPackageRows'])->first(
            fn ($row) => str_contains((string) ($row['name'] ?? ''), 'Night Safari')
        );
        $this->assertSame(2, (int) $supp['multiplier']);
        $this->assertSame(60.0, (float) $supp['total']);
    }

    public function test_order_line_sell_totals_sum_active_orders(): void
    {
        $order = (object) [
            'status' => 1,
            'type' => 'attraction',
            'currency' => 'SGD',
            'booking_id' => 'B1',
            'data' => [
                'totalPrice' => 50,
                'AttractionName' => 'Zoo',
            ],
        ];
        $payload = $this->makePricedPayload([
            'orders' => collect([$order]),
        ]);

        $view = (new QuotationPresentationAssembler)->assemble($payload, 'itinerary');
        $merged = app(QuotationDataBuilder::class)->forViewName($payload, 'single-tour-package.quotation');

        $this->assertCount(1, $view['quotationOrderRows']);
        $this->assertSame(50.0, (float) $view['quotationOrderRows'][0]['amount']);
        $this->assertSame(50.0, (float) $view['overallQuotationTotal']);
        $this->assertSame(50.0, (float) $merged['quotationData']['pricing']['order_line_sell_total']);
        $this->assertArrayNotHasKey('quotationOrderRows', $merged);
        $this->assertArrayNotHasKey('otherOrderTotalByBucketKey', $merged);
        $this->assertArrayNotHasKey('convertToOverall', $merged);
    }

    public function test_failed_currency_conversion_keeps_native_amount(): void
    {
        $native = 80.0;
        $converted = \App\Helpers\CurrencyHelper::convertAmount($native, 'ZZZ', 'SGD');
        $payload = $this->makePricedPayload(array_merge($this->adultOnlySharing(), [
            'tour' => $this->makeTour(['adult' => 2, 'child' => 0]),
            'country_sharing' => [[
                'country' => 'Singapore',
                'city' => 'Singapore',
                'currency' => 'ZZZ',
                'hotel_single' => 0,
                'hotel_double' => $native,
                'hotel_triple' => 0,
                'other_services_single' => 0,
                'other_services_double' => 0,
                'other_services_child' => 0,
                'child_bed_total' => 0,
            ]],
        ]));
        $payload['bookingDetails']['no_of_adults'] = 2;
        $payload['bookingDetails']['no_of_children'] = 0;

        $view = (new QuotationPresentationAssembler)->assemble($payload, 'itinerary');
        $hotelLine = $view['overallPackageRows'][0]['services'][0] ?? null;

        $this->assertNotNull($hotelLine);
        $expectedUnit = $converted === null ? $native : (float) ceil($converted);
        $this->assertSame($expectedUnit, (float) $hotelLine['price']);
        if ($converted === null) {
            $this->assertSame(160.0, (float) $view['overallLinesSubtotal']);
        }
    }

    public function test_dmc_and_agency_branding_and_agency_fallback(): void
    {
        $builder = app(QuotationDataBuilder::class);
        $dmc = $builder->forViewName($this->makePayload(), 'single-tour-package.quotation');
        $this->assertSame('dmc', $dmc['quotationData']['branding']['mode']);
        $this->assertSame('Test DMC', $dmc['quotationData']['branding']['company_name']);

        $agency = (object) [
            'agency_name' => 'Agent Co',
            'address' => '2 Agency Road',
            'email' => 'agency@example.com',
            'phone' => '+65 222',
            'logo' => 'agency-logo.png',
        ];
        $agencyView = $builder->forViewName($this->makePayload([
            'logoType' => 'agency',
            'user_agency' => $agency,
            'agentDetails' => ['name' => 'Ignored'],
        ]), 'single-tour-package.quotation');
        $this->assertSame('agency', $agencyView['quotationData']['branding']['mode']);
        $this->assertSame('Agent Co', $agencyView['quotationData']['branding']['company_name']);
        $this->assertSame('agency-logo.png', $agencyView['quotationData']['branding']['logo']);

        $fallback = $builder->forViewName($this->makePayload([
            'logoType' => 'agency',
            'user_agency' => null,
        ]), 'single-tour-package.quotation');
        $this->assertSame('agency', $fallback['quotationData']['branding']['mode']);
        $this->assertSame('Test DMC', $fallback['quotationData']['branding']['company_name']);
    }

    public function test_packaged_and_itinerary_blades_render_prepared_share_cells(): void
    {
        $payload = $this->makePricedPayload();
        $builder = app(QuotationDataBuilder::class);

        $itineraryHtml = view(
            'single-tour-package.quotation',
            $builder->forViewName($payload, 'single-tour-package.quotation')
        )->render();
        $detailedHtml = view(
            'single-tour-package.detailedqutation',
            $builder->forViewName($payload, 'single-tour-package.detailedqutation')
        )->render();

        $this->assertStringContainsString('SGD 100(Adult)', $itineraryHtml);
        $this->assertStringContainsString('SGD 40(Child)', $itineraryHtml);
        $this->assertStringContainsString('SGD 20(Adult)', $itineraryHtml);
        $this->assertStringContainsString('Hotel-Accommodation (per pax)', $itineraryHtml);
        $this->assertStringContainsString('SGD 120(Adult)', $detailedHtml);
        $this->assertStringContainsString('SGD 55(Child)', $detailedHtml);
        $this->assertStringContainsString('Hotel-Accommodation &amp; Other Services (per pax)', $detailedHtml);
        $this->assertSame(
            'detailed',
            $builder->forViewName($payload, 'single-tour-package.detailedqutation')['quotationData']['quotation']['type']
        );
        $this->assertNotEmpty(
            $builder->forViewName($payload, 'single-tour-package.detailedqutation')['quotationData']['pricing']['country_sharing_combined']
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function makePricedPayload(array $overrides = []): array
    {
        $tourPrices = array_merge([
            'single_sharing' => 170,
            'double_sharing' => 120,
            'triple_sharing' => 0,
            'other_services_single' => 20,
            'other_services_double' => 20,
            'hotel_price_options' => $overrides['hotel_price_options'] ?? [[
                'hotel_name' => 'Marina Bay',
                'country' => 'Singapore',
                'city' => 'Singapore',
                'currency' => 'SGD',
                'selected_persons' => 2,
                'number_of_rooms' => 1,
                'children_with_bed' => 1,
                'child_with_bed_total' => 40,
            ]],
            'country_sharing' => $overrides['country_sharing'] ?? [[
                'country' => 'Singapore',
                'city' => 'Singapore',
                'currency' => 'SGD',
                'hotel_single' => 150,
                'hotel_double' => 100,
                'hotel_triple' => 0,
                'other_services_single' => 20,
                'other_services_double' => 20,
                'other_services_child' => 15,
                'child_bed_total' => 40,
            ]],
            'discounts' => [],
            'discount_total' => 0,
            'supplements' => [],
        ], $overrides['tourPrices'] ?? []);

        if (!isset($overrides['tourPrices']['hotel_price_options']) && isset($overrides['hotel_price_options'])) {
            $tourPrices['hotel_price_options'] = $overrides['hotel_price_options'];
        }
        if (!isset($overrides['tourPrices']['country_sharing']) && isset($overrides['country_sharing'])) {
            $tourPrices['country_sharing'] = $overrides['country_sharing'];
        }
        if (!isset($overrides['tourPrices']['supplements']) && isset($overrides['tourPrices'])) {
            $tourPrices['supplements'] = $overrides['tourPrices']['supplements'] ?? $tourPrices['supplements'];
        }

        unset($overrides['tourPrices'], $overrides['hotel_price_options'], $overrides['country_sharing']);

        return $this->makePayload(array_merge([
            'tourPrices' => $tourPrices,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function adultOnlySharing(): array
    {
        return [
            'hotel_price_options' => [[
                'hotel_name' => 'Marina Bay',
                'country' => 'Singapore',
                'city' => 'Singapore',
                'currency' => 'SGD',
                'selected_persons' => 2,
                'number_of_rooms' => 1,
            ]],
            'country_sharing' => [[
                'country' => 'Singapore',
                'city' => 'Singapore',
                'currency' => 'SGD',
                'hotel_single' => 150,
                'hotel_double' => 100,
                'hotel_triple' => 0,
                'other_services_single' => 20,
                'other_services_double' => 20,
                'other_services_child' => 0,
                'child_bed_total' => 0,
            ]],
        ];
    }
}
