<?php

namespace Tests\Feature;

use App\Services\HotelSuppliers\RecheckPriceComparison;
use App\Services\OnlinePricing\Appliers\HotelPriceMarkupApplier;
use App\Services\OnlinePricing\MarkupContext;
use App\Services\OnlinePricing\MarkupRule;
use Tests\TestCase;

class RecheckPriceComparisonTest extends TestCase
{
    public function test_it_records_the_markup_stack_on_the_room_at_enquiry_time(): void
    {
        $context = new MarkupContext(
            adminRule: new MarkupRule('percentage', 5.0, 'admin:supplier:mg_bedbank'),
            supplierCode: 'mg_bedbank',
        );

        $hotels = app(HotelPriceMarkupApplier::class)->apply([
            [
                'rooms' => [
                    [
                        'price' => ['actual' => 912.86, 'taxValue' => 0],
                        'booking' => ['room' => ['net_price' => 912.86]],
                    ],
                ],
            ],
        ], $context);

        $room = $hotels[0]['rooms'][0];

        $this->assertSame(959.0, $room['price']['actual']);
        $this->assertSame(912.86, $room['booking']['room']['net_price'], 'raw supplier price must stay net');
        $this->assertSame($context->toArray(), $room['markup']);
        $this->assertSame($context->toArray(), $room['booking']['markup']);
    }

    public function test_replaying_the_stored_markup_reproduces_the_quoted_price(): void
    {
        $result = app(RecheckPriceComparison::class)->compare(
            $this->booking(quotedPrice: 959, markup: $this->adminFivePercent()),
            supplierPrice: 912.86,
            storedSupplierPrice: 912.86,
        );

        $this->assertSame(959.0, $result['customer_price']);
        $this->assertSame('customer', $result['comparison_basis']);
        $this->assertFalse($result['price_changed']);
    }

    public function test_it_flags_a_real_increase_in_customer_terms(): void
    {
        $result = app(RecheckPriceComparison::class)->compare(
            $this->booking(quotedPrice: 959, markup: $this->adminFivePercent()),
            supplierPrice: 1000.0,
            storedSupplierPrice: 912.86,
        );

        $this->assertSame(1050.0, $result['customer_price']);
        $this->assertTrue($result['price_changed']);
        $this->assertSame(['admin:supplier:mg_bedbank 5%'], $result['markup_rules']);
    }

    public function test_enquiries_without_a_recorded_stack_still_apply_live_markups(): void
    {
        $result = app(RecheckPriceComparison::class)->compare(
            $this->booking(quotedPrice: 959, markup: null),
            supplierPrice: 912.86,
            storedSupplierPrice: 912.86,
        );

        $this->assertTrue($result['markup_applied']);
        $this->assertNotNull($result['customer_price']);
        $this->assertSame('customer', $result['comparison_basis']);
        $this->assertGreaterThan($result['stored_supplier_price'], $result['customer_price']);
    }

    public function test_it_reports_no_basis_when_nothing_comparable_was_stored(): void
    {
        $result = app(RecheckPriceComparison::class)->compare(
            $this->booking(quotedPrice: 0, markup: null),
            supplierPrice: 912.86,
            storedSupplierPrice: null,
        );

        $this->assertSame('none', $result['comparison_basis']);
        $this->assertFalse($result['price_changed']);
    }

    public function test_it_uses_the_tour_dmc_when_the_session_has_no_dmc_rule(): void
    {
        $tour = \App\Models\Tour::query()->where('dmc_id', 72)->orderByDesc('id')->first();

        if (! $tour) {
            $this->markTestSkipped('No tour owned by DMC 72 is available.');
        }

        $result = app(RecheckPriceComparison::class)->compare(
            $this->booking(quotedPrice: 2639, markup: null),
            supplierPrice: 2417.25,
            storedSupplierPrice: 2417.25,
            options: ['tour_id' => (int) ($tour->tour_id ?: $tour->id)],
        );

        $this->assertTrue($result['markup_applied']);
        $this->assertStringContainsString('dmc:hotel', implode(' ', $result['markup_rules']));
        $this->assertSame(2639.0, $result['customer_price']);
        $this->assertFalse($result['price_changed']);
    }

    public function test_a_flat_dmc_markup_stacks_on_top_of_the_admin_rule(): void
    {
        $result = app(RecheckPriceComparison::class)->compare(
            $this->booking(quotedPrice: 1059, markup: [
                'admin_rule' => ['type' => 'percentage', 'amount' => 5.0, 'source' => 'admin:supplier:mg_bedbank'],
                'dmc_rule' => ['type' => 'flat', 'amount' => 100.0, 'source' => 'dmc:hotel'],
                'dmc_id' => 72,
                'supplier_code' => 'mg_bedbank',
                'service_type' => 'hotels',
            ]),
            supplierPrice: 912.86,
            storedSupplierPrice: 912.86,
        );

        // 912.86 + 5% = 958.50, + 100 flat = 1058.50, rounded up to the whole unit.
        $this->assertSame(1059.0, $result['customer_price']);
        $this->assertFalse($result['price_changed']);
    }

    /**
     * @return array<string, mixed>
     */
    private function adminFivePercent(): array
    {
        return [
            'admin_rule' => ['type' => 'percentage', 'amount' => 5.0, 'source' => 'admin:supplier:mg_bedbank'],
            'dmc_rule' => null,
            'dmc_id' => null,
            'supplier_code' => 'mg_bedbank',
            'service_type' => 'hotels',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $markup
     * @return array<string, mixed>
     */
    private function booking(float $quotedPrice, ?array $markup): array
    {
        $online = [
            'supplier_code' => 'mg_bedbank',
            'room' => ['net_price' => 912.86],
            'raw_room' => ['netPrice' => 912.86, 'grossPrice' => 912.86],
        ];

        if ($markup !== null) {
            $online['markup'] = $markup;
        }

        return [
            'totalPrice' => $quotedPrice,
            'onlineHotelBooking' => $online,
        ];
    }
}
