<?php

namespace App\Console\Commands;

use App\Helpers\CommonHelper;
use App\Models\Tour;
use App\Services\Quotations\QuotationTemplateCssSanitizer;
use App\Services\Quotations\QuotationTemplateHtmlSanitizer;
use App\Services\Quotations\QuotationTemplateRenderException;
use App\Services\Quotations\QuotationTemplateRenderer;
use App\Services\Quotations\QuotationTemplateResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Validates a local HTML/CSS draft against the sandboxed Twig renderer
 * without writing anything to quotation_templates / versions.
 *
 * With --tour=, uses the same quotation data path as PDF download.
 */
class CheckQuotationTemplateDraft extends Command
{
    protected $signature = 'quotation-template:check-draft
                            {name=acco-service : Draft basename under resources/quotation-templates/drafts (without extension)}
                            {--type=itinerary : Quotation type (itinerary|detailed)}
                            {--tour= : Real tour_id for full DMC quotation data}
                            {--currency= : Optional target currency}
                            {--logo-type=dmc : Branding mode dmc|agency}
                            {--pdf : Also write a PDF preview file}';

    protected $description = 'Render a local quotation template HTML/CSS draft through the secure renderer (no database save)';

    public function handle(): int
    {
        $name = basename((string) $this->argument('name'));
        $type = (string) $this->option('type');

        if ($name === 'detailed' && $type === 'itinerary') {
            $type = 'detailed';
        }

        if (! in_array($type, ['itinerary', 'detailed'], true)) {
            $this->error('Invalid --type. Use itinerary or detailed.');

            return self::FAILURE;
        }

        $base = resource_path('quotation-templates/drafts/'.$name);
        $htmlPath = $base.'.html';
        $cssPath = $base.'.css';

        if (! File::isFile($htmlPath)) {
            $this->error('Missing HTML draft: '.$htmlPath);

            return self::FAILURE;
        }

        $html = File::get($htmlPath);
        $css = File::isFile($cssPath) ? File::get($cssPath) : '';
        $outDir = storage_path('app/quotation-template-drafts');
        File::ensureDirectoryExists($outDir);

        $tourId = $this->option('tour');
        if ($tourId !== null && $tourId !== '') {
            return $this->renderWithTour(
                (int) $tourId,
                $name,
                $html,
                $css,
                $type,
                $outDir
            );
        }

        return $this->renderWithSampleData($name, $html, $css, $type, $outDir);
    }

    private function renderWithTour(
        int $tourId,
        string $name,
        string $html,
        string $css,
        string $type,
        string $outDir
    ): int {
        if (! Tour::query()->where('tour_id', $tourId)->exists()) {
            $this->error('Tour not found for tour_id '.$tourId);

            return self::FAILURE;
        }

        $viewName = $type === 'detailed'
            ? 'single-tour-package.detailedqutation'
            : 'single-tour-package.quotation';
        $currency = $this->option('currency') ? strtoupper((string) $this->option('currency')) : null;
        $logoType = (string) $this->option('logo-type') === 'agency' ? 'agency' : 'dmc';

        try {
            $response = CommonHelper::previewLocalQuotationTemplateDraft(
                $tourId,
                $html,
                $css,
                $currency,
                $viewName,
                $logoType,
                false
            );
        } catch (QuotationTemplateRenderException $e) {
            $this->error('Draft failed validation: '.$e->getMessage());

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Draft preview failed: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($response === null) {
            $this->error('Unable to build quotation data for this tour.');

            return self::FAILURE;
        }

        $outPath = $outDir.DIRECTORY_SEPARATOR.$name.'-tour-'.$tourId.'-preview.html';
        File::put($outPath, $response->getContent());

        $this->info('Draft rendered with real tour data (tour_id='.$tourId.').');
        $this->line('HTML preview: '.$outPath);

        if ($this->option('pdf')) {
            $pdfResponse = CommonHelper::previewLocalQuotationTemplateDraft(
                $tourId,
                $html,
                $css,
                $currency,
                $viewName,
                $logoType,
                true
            );
            if ($pdfResponse !== null) {
                $pdfPath = $outDir.DIRECTORY_SEPARATOR.$name.'-tour-'.$tourId.'-preview.pdf';
                File::put($pdfPath, $pdfResponse->getContent());
                $this->line('PDF preview: '.$pdfPath);
            }
        }

        $this->comment('Open the HTML file in a browser. If it looks good, copy '.$name.'.html / '.$name.'.css into Quotation Template Studio and Save Draft.');
        $this->comment('Browser (logged in as Super Admin): /quotation-templates/drafts/'.$name.'/preview?tour_id='.$tourId);

        return self::SUCCESS;
    }

    private function renderWithSampleData(
        string $name,
        string $html,
        string $css,
        string $type,
        string $outDir
    ): int {
        $renderer = new QuotationTemplateRenderer(
            new QuotationTemplateCssSanitizer(),
            new QuotationTemplateHtmlSanitizer(new QuotationTemplateCssSanitizer())
        );

        $resolved = [
            'source' => QuotationTemplateResolver::SOURCE_CUSTOM,
            'dmc_id' => 0,
            'quotation_type' => $type,
            'template_id' => null,
            'version_id' => null,
            'version_number' => null,
            'html_content' => $html,
            'css_content' => $css,
            'identifier' => 'draft:'.$name,
            'default_view' => 'single-tour-package.quotation',
        ];

        try {
            $output = $renderer->render($resolved, $this->sampleQuotationData($type));
        } catch (QuotationTemplateRenderException $e) {
            $this->error('Draft failed validation: '.$e->getMessage());

            return self::FAILURE;
        }

        $outPath = $outDir.DIRECTORY_SEPARATOR.$name.'-preview.html';
        File::put($outPath, $output);

        $this->info('Draft rendered with sample data only.');
        $this->line('Preview: '.$outPath);
        $this->warn('For full DMC quotation data, pass --tour=TOUR_ID (same payload as PDF download).');
        $this->comment('If the preview looks good, copy '.$name.'.html / '.$name.'.css into Quotation Template Studio and Save Draft.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleQuotationData(string $type): array
    {
        return [
            'quotation' => [
                'reference' => 'DRAFT-CHECK-001',
                'date' => '19 Jan 2026',
                'valid_until' => 'N/A',
                'currency' => 'USD',
                'base_currency' => 'SGD',
                'exchange_rate' => 1.0,
                'type' => $type,
                'generated_at' => null,
            ],
            'branding' => [
                'mode' => 'dmc',
                'company_name' => 'Sample DMC',
                'logo' => null,
                'address' => '1 Sample Street',
                'email' => 'ops@sample.test',
                'phone' => '+65 0000 0000',
                'dmc_details' => [],
                'agent_details' => [
                    'name' => 'Sample Agency',
                    'address' => 'N/A',
                    'contact_person' => 'N/A',
                    'phone' => 'N/A',
                    'email' => 'N/A',
                ],
            ],
            'booking' => [
                'lead_guest_name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => null,
                'adults' => 2,
                'children' => 0,
                'infants' => 0,
                'display_adults' => 2,
                'pax_text' => '2 Adults, 0 Children, 0 Infants',
                'foc_size' => null,
                'has_foc' => false,
                'passengers' => [],
            ],
            'travel' => [
                'destination' => 'Singapore',
                'start_date' => '01 Feb 2026',
                'end_date' => '05 Feb 2026',
                'duration' => '4N/5D',
                'travelling_date' => '01 Feb 2026 - 05 Feb 2026',
                'inclusion_date_range' => '',
                'rooming_text' => '',
            ],
            'services' => [
                'by_type' => [],
                'by_date' => [],
                'country_keys' => ['singapore|sgd'],
                'country_meta' => [
                    'singapore|sgd' => [
                        'country' => 'Singapore',
                        'city' => 'Singapore',
                        'currency' => 'SGD',
                        'date_range' => '01–05 Feb 2026',
                        'is_return' => false,
                    ],
                ],
                'hotels_by_country' => [
                    'singapore|sgd' => [
                        [
                            'hotel_name' => 'Grand Hotel',
                            'room_category' => 'Deluxe',
                            'room_count' => 1,
                            'total_pax' => 2,
                            'selected_persons' => 2,
                            'date_range' => '01–05 Feb 2026',
                            'occupancy_bits' => ['2 Adults'],
                        ],
                    ],
                ],
                'other_by_country' => [
                    'singapore|sgd' => [
                        'attractions' => [],
                        'restaurants' => [],
                        'arrivals' => [],
                        'departures' => [],
                        'local_transfers' => [],
                        'point_to_point' => [],
                        'hourly' => [],
                        'guides' => [],
                    ],
                ],
            ],
            'pricing' => [
                'display_currency' => 'USD',
                'country_sharing' => [],
                'package_rows' => [],
                'subtotal' => 1234.5,
                'discount_total' => 0,
                'tax_total' => 0,
                'grand_total' => 1234.5,
                'grand_total_label' => 'USD 1,234.50',
                'payment_terms' => [],
            ],
            'bank_details' => [
                'account_name' => 'Sample DMC',
                'account_number' => '000111',
                'bank_address' => 'Sample Bank',
                'ifsc' => '',
                'swift_bic_iban' => 'SAMPSGSG',
                'bank_code' => '',
                'branch_code' => '',
                'aba_routing' => '',
            ],
            'terms_and_conditions' => 'Sample terms for draft checking only.',
            'exclusions' => 'Sample exclusions.',
            'quotation_information' => null,
        ];
    }
}
