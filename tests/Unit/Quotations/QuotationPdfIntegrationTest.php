<?php

namespace Tests\Unit\Quotations;

use App\Helpers\CommonHelper;
use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateVersion;
use App\Models\Tour;
use App\Services\Quotations\QuotationDataBuilder;
use App\Services\Quotations\QuotationTemplateResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class QuotationPdfIntegrationTest extends TestCase
{
    private const PNG_1X1 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->unsignedBigInteger('userId')->primary();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_id')->nullable();
            $table->unsignedBigInteger('dmc_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('master_dmc_id')->nullable();
            $table->string('display_id')->nullable();
            $table->string('destination')->nullable();
            $table->integer('adult')->nullable();
            $table->integer('child')->nullable();
            $table->integer('infant')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $migration = require database_path('migrations/2026_11_02_161000_create_quotation_template_tables.php');
        $migration->up();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_published_custom_itinerary_template_uses_load_html(): void
    {
        $dmcId = $this->insertUser(501, 'DMC One');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<p>Custom Itinerary {{ branding.company_name }}</p>');
        $this->activate($template, $version);

        $pdf = $this->mockPdfExpectingLoadHtml('Custom Itinerary');

        $response = $this->invokeStreamOrDownload(
            'single-tour-package.quotation',
            $this->viewDataForTour($tour, ['dmcCompanyName' => 'Acme DMC']),
            false
        );

        $this->assertSame(200, $response->getStatusCode());
        $pdf->shouldHaveReceived('setOption')->with('enable-php', false);
        $pdf->shouldHaveReceived('setOption')->with('isRemoteEnabled', false);
        $pdf->shouldHaveReceived('setPaper')->with('a4');
        $pdf->shouldHaveReceived('download')->with('tour-quotation.pdf');
    }

    public function test_published_custom_detailed_template_uses_load_html(): void
    {
        $dmcId = $this->insertUser(502, 'DMC Two');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_DETAILED);
        $version = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<p>Custom Detailed {{ quotation.reference }}</p>');
        $this->activate($template, $version);

        $this->mockPdfExpectingLoadHtml('Custom Detailed');

        $response = $this->invokeStreamOrDownload(
            'single-tour-package.detailedqutation',
            $this->viewDataForTour($tour),
            true
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_no_custom_template_uses_blade_fallback(): void
    {
        $dmcId = $this->insertUser(503, 'DMC Three');
        $tour = $this->makeTour($dmcId);

        $this->mockPdfExpectingLoadView('single-tour-package.quotation');

        $response = $this->invokeStreamOrDownload(
            'single-tour-package.quotation',
            $this->viewDataForTour($tour),
            false
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_custom_itinerary_template_is_not_used_for_detailed_quotation(): void
    {
        $dmcId = $this->insertUser(504, 'DMC Four');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<p>Itinerary Only</p>');
        $this->activate($template, $version);

        $this->mockPdfExpectingLoadView('single-tour-package.detailedqutation');

        $response = $this->invokeStreamOrDownload(
            'single-tour-package.detailedqutation',
            $this->viewDataForTour($tour),
            false
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_custom_detailed_template_is_not_used_for_itinerary_quotation(): void
    {
        $dmcId = $this->insertUser(505, 'DMC Five');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_DETAILED);
        $version = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<p>Detailed Only</p>');
        $this->activate($template, $version);

        $this->mockPdfExpectingLoadView('single-tour-package.quotation');

        $response = $this->invokeStreamOrDownload(
            'single-tour-package.quotation',
            $this->viewDataForTour($tour),
            false
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_dmc_ownership_comes_from_tour_not_request_style_ids(): void
    {
        $owningDmcId = $this->insertUser(506, 'Owner DMC');
        $otherDmcId = $this->insertUser(507, 'Other DMC');
        $tour = $this->makeTour($owningDmcId);

        $ownTemplate = $this->makeTemplate($owningDmcId, QuotationTemplate::TYPE_ITINERARY);
        $ownVersion = $this->makeVersion($ownTemplate, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<p>Owner Template</p>');
        $this->activate($ownTemplate, $ownVersion);

        $otherTemplate = $this->makeTemplate($otherDmcId, QuotationTemplate::TYPE_ITINERARY);
        $otherVersion = $this->makeVersion($otherTemplate, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<p>Other Template</p>');
        $this->activate($otherTemplate, $otherVersion);

        $this->mockPdfExpectingLoadHtml('Owner Template');

        $viewData = $this->viewDataForTour($tour);
        $viewData['request_dmc_id'] = $otherDmcId;
        $viewData['logged_in_agent_as_dmc'] = $otherDmcId;

        $response = $this->invokeStreamOrDownload('single-tour-package.quotation', $viewData, false);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_missing_dmc_uses_system_blade_default(): void
    {
        $tour = $this->makeTour(null);

        $this->mockPdfExpectingLoadView('single-tour-package.quotation');

        $response = $this->invokeStreamOrDownload(
            'single-tour-package.quotation',
            $this->viewDataForTour($tour),
            false
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_invalid_custom_html_falls_back_to_blade(): void
    {
        $dmcId = $this->insertUser(508, 'DMC Fail HTML');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion(
            $template,
            1,
            QuotationTemplateVersion::STATUS_PUBLISHED,
            '<script>alert(1)</script><p>Hi</p>'
        );
        $this->activate($template, $version);

        Log::spy();
        $this->mockPdfExpectingLoadView('single-tour-package.quotation');

        $response = $this->invokeStreamOrDownload(
            'single-tour-package.quotation',
            $this->viewDataForTour($tour),
            false
        );

        $this->assertSame(200, $response->getStatusCode());
        Log::shouldHaveReceived('warning')->withArgs(function (string $message, array $context) {
            return str_contains($message, 'Custom quotation template render failed')
                && ! array_key_exists('html', $context)
                && ! array_key_exists('lead_guest_name', $context);
        })->atLeast()->once();
    }

    public function test_invalid_custom_css_falls_back_to_blade(): void
    {
        $dmcId = $this->insertUser(509, 'DMC Fail CSS');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion(
            $template,
            1,
            QuotationTemplateVersion::STATUS_PUBLISHED,
            '<p>Hi</p>',
            'p { font-family: "</style><script>alert(1)</script>"; }'
        );
        $this->activate($template, $version);

        $this->mockPdfExpectingLoadView('single-tour-package.quotation');

        $response = $this->invokeStreamOrDownload(
            'single-tour-package.quotation',
            $this->viewDataForTour($tour),
            false
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_twig_render_failure_falls_back_to_blade(): void
    {
        $dmcId = $this->insertUser(510, 'DMC Fail Twig');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion(
            $template,
            1,
            QuotationTemplateVersion::STATUS_PUBLISHED,
            '{{ booking.missing_field }}'
        );
        $this->activate($template, $version);

        $this->mockPdfExpectingLoadView('single-tour-package.quotation');

        $response = $this->invokeStreamOrDownload(
            'single-tour-package.quotation',
            $this->viewDataForTour($tour),
            false
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_system_resolver_result_uses_blade_and_not_load_html(): void
    {
        $dmcId = $this->insertUser(511, 'DMC Draft Only');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_DRAFT, '<p>Draft</p>');
        $this->activate($template, $version);

        Pdf::shouldReceive('loadHTML')->never();
        $this->mockPdfExpectingLoadView('single-tour-package.quotation');

        $response = $this->invokeStreamOrDownload(
            'single-tour-package.quotation',
            $this->viewDataForTour($tour),
            false
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_unrelated_pdf_exception_is_not_converted_to_template_fallback(): void
    {
        $dmcId = $this->insertUser(512, 'DMC PDF Boom');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion($template, 1, QuotationTemplateVersion::STATUS_PUBLISHED, '<p>OK {{ branding.company_name }}</p>');
        $this->activate($template, $version);

        Pdf::shouldReceive('loadHTML')
            ->once()
            ->andThrow(new \RuntimeException('DomPDF exploded'));
        Pdf::shouldReceive('loadView')->never();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DomPDF exploded');

        $this->invokeStreamOrDownload(
            'single-tour-package.quotation',
            $this->viewDataForTour($tour, ['dmcCompanyName' => 'Acme']),
            false
        );
    }

    public function test_dmc_and_agency_branding_reach_normalized_quotation_data(): void
    {
        $dmcPayload = $this->viewDataForTour($this->makeTour($this->insertUser(513, 'Brand DMC')), [
            'logoType' => 'dmc',
            'dmcCompanyName' => 'DMC Brand Co',
            'dmcLogo' => self::PNG_1X1,
        ]);
        $dmcView = app(QuotationDataBuilder::class)->forViewName($dmcPayload, 'single-tour-package.quotation');
        $this->assertSame('dmc', $dmcView['quotationData']['branding']['mode']);
        $this->assertSame('DMC Brand Co', $dmcView['quotationData']['branding']['company_name']);
        $this->assertSame(self::PNG_1X1, $dmcView['quotationData']['branding']['logo']);

        $agency = (object) [
            'agency_name' => 'Agency Brand Co',
            'address' => 'Agency Street',
            'email' => 'agency@example.com',
            'phone' => '+65 999',
            'logo' => self::PNG_1X1,
        ];
        $agencyPayload = $this->viewDataForTour($this->makeTour($this->insertUser(514, 'Brand DMC 2')), [
            'logoType' => 'agency',
            'user_agency' => $agency,
            'dmcCompanyName' => 'Should Not Win',
            'dmcLogo' => null,
        ]);
        $agencyView = app(QuotationDataBuilder::class)->forViewName($agencyPayload, 'single-tour-package.quotation');
        $this->assertSame('agency', $agencyView['quotationData']['branding']['mode']);
        $this->assertSame('Agency Brand Co', $agencyView['quotationData']['branding']['company_name']);
        $this->assertSame(self::PNG_1X1, $agencyView['quotationData']['branding']['logo']);
    }

    public function test_missing_agency_branding_falls_back_to_dmc_in_helper_payload(): void
    {
        $payload = $this->viewDataForTour($this->makeTour($this->insertUser(515, 'Fallback DMC')), [
            'logoType' => 'dmc',
            'user_agency' => null,
            'dmcCompanyName' => 'Fallback DMC Co',
        ]);
        $view = app(QuotationDataBuilder::class)->forViewName($payload, 'single-tour-package.quotation');

        $this->assertSame('dmc', $view['quotationData']['branding']['mode']);
        $this->assertSame('Fallback DMC Co', $view['quotationData']['branding']['company_name']);
    }

    public function test_itinerary_and_detailed_quotation_data_remain_distinct(): void
    {
        $tour = $this->makeTour($this->insertUser(516, 'Type DMC'));
        $payload = $this->viewDataForTour($tour);

        $itinerary = app(QuotationDataBuilder::class)->forViewName($payload, 'single-tour-package.quotation');
        $detailed = app(QuotationDataBuilder::class)->forViewName($payload, 'single-tour-package.detailedqutation');

        $this->assertSame('itinerary', $itinerary['quotationData']['quotation']['type']);
        $this->assertSame('detailed', $detailed['quotationData']['quotation']['type']);
    }

    public function test_currency_and_pricing_match_phase_one_contract_keys(): void
    {
        $payload = $this->viewDataForTour($this->makeTour($this->insertUser(517, 'Price DMC')), [
            'selectedCurrency' => 'USD',
            'baseCurrency' => 'SGD',
            'exchangeRate' => 1.25,
        ]);
        $view = app(QuotationDataBuilder::class)->forViewName($payload, 'single-tour-package.quotation');
        $quotationData = $view['quotationData'];

        $this->assertSame('USD', $quotationData['quotation']['currency']);
        $this->assertSame('SGD', $quotationData['quotation']['base_currency']);
        $this->assertSame(1.25, $quotationData['quotation']['exchange_rate']);
        $this->assertArrayHasKey('pricing', $quotationData);
        $this->assertArrayHasKey('grand_total', $quotationData['pricing']);
        $this->assertArrayHasKey('display_currency', $quotationData['pricing']);
    }

    public function test_gd_retry_clears_logo_before_second_render(): void
    {
        $dmcId = $this->insertUser(518, 'GD DMC');
        $tour = $this->makeTour($dmcId);
        $template = $this->makeTemplate($dmcId, QuotationTemplate::TYPE_ITINERARY);
        $version = $this->makeVersion(
            $template,
            1,
            QuotationTemplateVersion::STATUS_PUBLISHED,
            '<p>{{ branding.company_name }}</p>{% if branding.logo %}<img src="{{ branding.logo }}" alt="logo" width="1" height="1">{% endif %}'
        );
        $this->activate($template, $version);

        $pdfWithLogo = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdfWithLogo->shouldReceive('setPaper')->andReturnSelf();
        $pdfWithLogo->shouldReceive('setOption')->andReturnSelf();
        $pdfWithLogo->shouldReceive('download')->once()->andThrow(new \Exception('The GD extension is required'));

        $pdfWithoutLogo = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdfWithoutLogo->shouldReceive('setPaper')->with('a4')->andReturnSelf();
        $pdfWithoutLogo->shouldReceive('setOption')->with('enable-php', false)->andReturnSelf();
        $pdfWithoutLogo->shouldReceive('setOption')->with('isHtml5ParserEnabled', true)->andReturnSelf();
        $pdfWithoutLogo->shouldReceive('setOption')->with('isRemoteEnabled', false)->andReturnSelf();
        $pdfWithoutLogo->shouldReceive('download')->once()->andReturn(new Response('pdf-bytes', 200));

        $firstHtml = null;
        $secondHtml = null;

        Pdf::shouldReceive('loadHTML')
            ->twice()
            ->andReturnUsing(function (string $html) use (&$firstHtml, &$secondHtml, $pdfWithLogo, $pdfWithoutLogo) {
                if ($firstHtml === null) {
                    $firstHtml = $html;

                    return $pdfWithLogo;
                }
                $secondHtml = $html;

                return $pdfWithoutLogo;
            });
        Pdf::shouldReceive('loadView')->never();

        $viewData = $this->viewDataForTour($tour, [
            'dmcCompanyName' => 'Logo DMC',
            'dmcLogo' => self::PNG_1X1,
        ]);

        try {
            $this->invokeStreamOrDownload('single-tour-package.quotation', $viewData, false);
            $this->fail('Expected GD exception from first PDF attempt.');
        } catch (\Exception $exception) {
            $this->assertStringContainsString('GD extension', $exception->getMessage());
        }

        $this->assertNotNull($firstHtml);
        $this->assertStringContainsString('data:image/png;base64,', $firstHtml);

        $viewData['dmcLogo'] = null;
        $response = $this->invokeStreamOrDownload('single-tour-package.quotation', $viewData, false);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($secondHtml);
        $this->assertStringNotContainsString('data:image/png;base64,', $secondHtml);
    }

    public function test_preview_uses_stream_filename(): void
    {
        $dmcId = $this->insertUser(519, 'Preview DMC');
        $tour = $this->makeTour($dmcId);

        $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->with('a4')->andReturnSelf();
        $pdf->shouldReceive('setOption')->andReturnSelf();
        $pdf->shouldReceive('stream')->once()->with('tour-quotation.pdf')->andReturn(new Response('preview', 200));
        $pdf->shouldReceive('download')->never();

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('single-tour-package.quotation', Mockery::type('array'))
            ->andReturn($pdf);

        $response = $this->invokeStreamOrDownload(
            'single-tour-package.quotation',
            $this->viewDataForTour($tour),
            true
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_resolver_default_view_is_used_on_fallback(): void
    {
        $dmcId = $this->insertUser(520, 'Default View DMC');
        $tour = $this->makeTour($dmcId);
        $resolved = app(QuotationTemplateResolver::class)->resolve($tour, QuotationTemplate::TYPE_DETAILED);
        $this->assertSame(QuotationTemplateResolver::SOURCE_SYSTEM, $resolved['source']);
        $this->assertSame('single-tour-package.detailedqutation', $resolved['default_view']);

        $this->mockPdfExpectingLoadView('single-tour-package.detailedqutation');
        $response = $this->invokeStreamOrDownload(
            'single-tour-package.detailedqutation',
            $this->viewDataForTour($tour),
            false
        );
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    private function invokeStreamOrDownload(string $viewName, array $viewData, bool $preview): Response
    {
        $method = new ReflectionMethod(CommonHelper::class, 'streamOrDownloadQuotationPdf');
        $method->setAccessible(true);

        /** @var Response $response */
        $response = $method->invoke(null, $viewName, $viewData, $preview);

        return $response;
    }

    /**
     * @return \Mockery\MockInterface&\Barryvdh\DomPDF\PDF
     */
    private function mockPdfExpectingLoadHtml(string $needle)
    {
        $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->with('a4')->andReturnSelf();
        $pdf->shouldReceive('setOption')->with('enable-php', false)->andReturnSelf();
        $pdf->shouldReceive('setOption')->with('isHtml5ParserEnabled', true)->andReturnSelf();
        $pdf->shouldReceive('setOption')->with('isRemoteEnabled', false)->andReturnSelf();
        $pdf->shouldReceive('download')->with('tour-quotation.pdf')->andReturn(new Response('pdf', 200))->byDefault();
        $pdf->shouldReceive('stream')->with('tour-quotation.pdf')->andReturn(new Response('pdf', 200))->byDefault();

        Pdf::shouldReceive('loadHTML')
            ->once()
            ->with(Mockery::on(function (string $html) use ($needle) {
                return str_contains($html, $needle)
                    && str_starts_with($html, '<!DOCTYPE html>')
                    && ! str_contains(strtolower($html), '<script');
            }))
            ->andReturn($pdf);
        Pdf::shouldReceive('loadView')->never();

        return $pdf;
    }

    private function mockPdfExpectingLoadView(string $viewName): void
    {
        $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->with('a4')->andReturnSelf();
        $pdf->shouldReceive('setOption')->with('enable-php', false)->andReturnSelf();
        $pdf->shouldReceive('setOption')->with('isHtml5ParserEnabled', true)->andReturnSelf();
        $pdf->shouldReceive('setOption')->with('isRemoteEnabled', false)->andReturnSelf();
        $pdf->shouldReceive('download')->with('tour-quotation.pdf')->andReturn(new Response('pdf', 200))->byDefault();
        $pdf->shouldReceive('stream')->with('tour-quotation.pdf')->andReturn(new Response('pdf', 200))->byDefault();

        Pdf::shouldReceive('loadHTML')->never();
        Pdf::shouldReceive('loadView')
            ->once()
            ->with($viewName, Mockery::on(function (array $data) {
                return isset($data['quotationData']) && is_array($data['quotationData']);
            }))
            ->andReturn($pdf);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function viewDataForTour(Tour $tour, array $overrides = []): array
    {
        return array_merge([
            'tour' => $tour,
            'orders' => collect(),
            'bookingDetails' => [
                'booking_id' => $tour->display_id ?? 'DMC-TEST',
                'lead_guest_name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '+65 111',
                'no_of_adults' => 2,
                'no_of_children' => 0,
                'no_of_infants' => 0,
            ],
            'travelDetails' => [
                'destination' => 'Singapore',
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
            'dmcLogo' => null,
            'agentDetails' => [],
            'proposalDetails' => [
                'proposal_date' => '01 Apr 2026',
                'proposal_validity' => 'N/A',
            ],
            'bankDetails' => [],
            'termsAndConditions' => '',
            'paymentTerms' => [],
            'quotationInformationHtml' => null,
            'generatedAt' => now(),
        ], $overrides);
    }

    private function insertUser(int $userId, string $name): int
    {
        DB::table('users')->insert([
            'userId' => $userId,
            'name' => $name,
            'email' => 'user'.$userId.'@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $userId;
    }

    private function makeTour(?int $dmcId): Tour
    {
        return Tour::query()->create([
            'tour_id' => random_int(10000, 99999),
            'dmc_id' => $dmcId,
            'agent_id' => 999,
            'display_id' => 'DMC-ORD'.random_int(1000, 9999),
            'destination' => 'Singapore',
            'adult' => 2,
            'child' => 0,
            'infant' => 0,
        ]);
    }

    private function makeTemplate(int $dmcId, string $type): QuotationTemplate
    {
        return QuotationTemplate::query()->create([
            'dmc_id' => $dmcId,
            'quotation_type' => $type,
        ]);
    }

    private function makeVersion(
        QuotationTemplate $template,
        int $version,
        string $status,
        string $html,
        string $css = 'body{color:#000}'
    ): QuotationTemplateVersion {
        return QuotationTemplateVersion::query()->create([
            'quotation_template_id' => $template->id,
            'version' => $version,
            'html_content' => $html,
            'css_content' => $css,
            'status' => $status,
            'published_at' => $status === QuotationTemplateVersion::STATUS_PUBLISHED ? now() : null,
        ]);
    }

    private function activate(QuotationTemplate $template, QuotationTemplateVersion $version): void
    {
        $template->active_version_id = $version->id;
        $template->save();
    }
}
