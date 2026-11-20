<?php

namespace App\Console\Commands;

use App\Helpers\CommonHelper;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\Tour;
use App\Services\InvoiceService;
use App\Services\Invoices\InvoicePdfService;
use App\Services\Invoices\InvoiceTemplateRenderException;
use App\Services\Invoices\InvoiceTemplateRenderer;
use App\Services\Invoices\InvoiceTemplateResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Validates a local HTML/CSS draft against the sandboxed Twig renderer
 * without writing anything to invoice_templates / versions.
 *
 * Prefer --tour= (same as Studio / quotation drafts). --invoice= still works.
 */
class CheckInvoiceTemplateDraft extends Command
{
    protected $signature = 'invoice-template:check-draft
                            {name=proforma : Draft basename under resources/invoice-templates/drafts (without extension)}
                            {--type=proforma : Invoice template type (proforma|proforma_price_only|final|final_price_only|alternate)}
                            {--tour= : Real tour_id (preferred; auto-ensures invoice after Prospect)}
                            {--invoice= : Real invoice_id for full invoice data}
                            {--currency= : Optional target currency}
                            {--logo-type=dmc : Branding mode dmc|agency}
                            {--pdf : Also write a PDF preview file}';

    protected $description = 'Render a local invoice template HTML/CSS draft through the secure renderer (no database save)';

    public function handle(InvoicePdfService $invoicePdfService, InvoiceService $invoiceService): int
    {
        $name = basename((string) $this->argument('name'));
        $type = (string) $this->option('type');

        $type = $this->normalizeTypeFromName($name, $type);

        if (! in_array($type, InvoiceTemplate::types(), true)) {
            $this->error('Invalid --type. Use: '.implode('|', InvoiceTemplate::types()));

            return self::FAILURE;
        }

        $base = resource_path('invoice-templates/drafts/'.$name);
        $htmlPath = $base.'.html';
        $cssPath = $base.'.css';

        if (! File::isFile($htmlPath)) {
            $this->error('Missing HTML draft: '.$htmlPath);

            return self::FAILURE;
        }

        $html = File::get($htmlPath);
        $css = File::isFile($cssPath) ? File::get($cssPath) : '';
        $outDir = storage_path('app/invoice-template-drafts');
        File::ensureDirectoryExists($outDir);

        $tourId = $this->option('tour');
        if ($tourId !== null && $tourId !== '') {
            return $this->renderWithTour(
                $invoicePdfService,
                $invoiceService,
                (int) $tourId,
                $name,
                $html,
                $css,
                $type,
                $outDir
            );
        }

        $invoiceId = $this->option('invoice');
        if ($invoiceId !== null && $invoiceId !== '') {
            return $this->renderWithInvoice(
                $invoicePdfService,
                (int) $invoiceId,
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
        InvoicePdfService $invoicePdfService,
        InvoiceService $invoiceService,
        int $tourId,
        string $name,
        string $html,
        string $css,
        string $type,
        string $outDir
    ): int {
        $tour = Tour::query()->where('tour_id', $tourId)->first();
        if ($tour === null) {
            $this->error('Tour not found for tour_id '.$tourId);

            return self::FAILURE;
        }

        $status = (string) ($tour->tour_status ?? '');
        if (! in_array($status, InvoiceService::invoiceEligibleStatuses(), true)) {
            $this->error(
                'Invoice preview requires tour status Tentative, Confirmed, Definite, or Actual (after Prospect). Current status: '
                .($status !== '' ? $status : 'N/A')
            );

            return self::FAILURE;
        }

        try {
            $invoice = $invoiceService->ensureInvoiceForTour($tourId);
        } catch (\Throwable $e) {
            $this->error('Unable to ensure invoice for tour '.$tourId.': '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Using invoice_id='.$invoice->invoice_id.' ('.$invoice->invoice_type.') for tour '.$tourId.' ['.$status.']');

        return $this->renderWithInvoice(
            $invoicePdfService,
            (int) $invoice->invoice_id,
            $name,
            $html,
            $css,
            $type,
            $outDir
        );
    }

    private function normalizeTypeFromName(string $name, string $type): string
    {
        $fromName = match ($name) {
            'proforma-price-only' => InvoiceTemplate::TYPE_PROFORMA_PRICE_ONLY,
            'final' => InvoiceTemplate::TYPE_FINAL,
            'final-price-only' => InvoiceTemplate::TYPE_FINAL_PRICE_ONLY,
            'alternate' => InvoiceTemplate::TYPE_ALTERNATE,
            'proforma' => InvoiceTemplate::TYPE_PROFORMA,
            default => null,
        };

        if ($fromName !== null && ($type === 'proforma' || $type === '' || $type === $fromName)) {
            return $fromName;
        }

        return $type;
    }

    private function renderWithInvoice(
        InvoicePdfService $invoicePdfService,
        int $invoiceId,
        string $name,
        string $html,
        string $css,
        string $type,
        string $outDir
    ): int {
        $invoice = Invoice::with(['tour', 'agent.agency', 'dmc', 'items'])
            ->where('invoice_id', $invoiceId)
            ->first();

        if ($invoice === null) {
            $this->error('Invoice not found for invoice_id '.$invoiceId);

            return self::FAILURE;
        }

        $currencyQuery = $this->option('currency') ? strtoupper((string) $this->option('currency')) : null;
        $logoType = (string) $this->option('logo-type') === 'agency' ? 'agency' : 'dmc';
        $mode = in_array($type, [
            InvoiceTemplate::TYPE_PROFORMA_PRICE_ONLY,
            InvoiceTemplate::TYPE_FINAL_PRICE_ONLY,
        ], true) ? 'price-only' : 'full';

        $currency = $this->buildInvoiceCurrencyContext($invoice, $currencyQuery);
        $defaultView = InvoiceTemplateResolver::DEFAULT_VIEWS[$type]
            ?? InvoiceTemplateResolver::DEFAULT_VIEWS[InvoiceTemplate::TYPE_PROFORMA];

        $viewData = array_merge($currency, [
            'invoice' => $invoice,
            'logoType' => $logoType,
            'mode' => $mode,
        ]);

        try {
            $response = $invoicePdfService->previewLocalDraft(
                $viewData,
                $html,
                $css,
                $type,
                $defaultView,
                false
            );
        } catch (InvoiceTemplateRenderException $e) {
            $this->error('Draft failed validation: '.$e->getMessage());

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Draft preview failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $outPath = $outDir.DIRECTORY_SEPARATOR.$name.'-invoice-'.$invoiceId.'-preview.html';
        File::put($outPath, $response->getContent());

        $this->info('Draft rendered with real invoice data (invoice_id='.$invoiceId.').');
        $this->line('HTML preview: '.$outPath);

        if ($this->option('pdf')) {
            try {
                $pdfResponse = $invoicePdfService->previewLocalDraft(
                    $viewData,
                    $html,
                    $css,
                    $type,
                    $defaultView,
                    true
                );
                $pdfPath = $outDir.DIRECTORY_SEPARATOR.$name.'-invoice-'.$invoiceId.'-preview.pdf';
                File::put($pdfPath, $pdfResponse->getContent());
                $this->line('PDF preview: '.$pdfPath);
            } catch (\Throwable $e) {
                $this->warn('PDF preview failed: '.$e->getMessage());
            }
        }

        $this->comment('Open the HTML file in a browser. If it looks good, copy '.$name.'.html / '.$name.'.css into Invoice Template Studio and Save Draft.');
        $tourIdHint = (int) ($invoice->tour_id ?? 0);
        $this->comment(
            'Browser (logged in as Super Admin): /invoice-templates/drafts/'.$name.'/preview?tour_id='
            .($tourIdHint > 0 ? $tourIdHint : '').($tourIdHint > 0 ? '' : 'invoice_id='.$invoiceId)
        );

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInvoiceCurrencyContext(Invoice $invoice, ?string $currencyQuery): array
    {
        $baseCurrency = CommonHelper::resolveInvoiceBaseCurrency($invoice);
        $selectedCurrency = CommonHelper::getInvoiceSelectedCurrency($currencyQuery, $invoice);
        $currencyConversion = CommonHelper::buildInvoiceCurrencyConversion($invoice, $selectedCurrency);
        $exchangeRate = CommonHelper::getInvoiceExchangeRate($baseCurrency, $selectedCurrency, $currencyConversion);
        $isThirdPartyInvoice = CommonHelper::isInvoiceThirdPartyEnabled($invoice);
        $invoiceMultiGeo = CommonHelper::detectInvoiceMultiGeo($invoice);
        $restrictedTpScope = CommonHelper::applyRestrictedThirdPartyInvoiceItemFilter($invoice);
        if (! empty($restrictedTpScope['restricted'])) {
            $invoiceMultiGeo = CommonHelper::detectInvoiceMultiGeo($invoice);
        }

        if ($isThirdPartyInvoice) {
            CommonHelper::enrichInvoiceItemsWithOrderGeo($invoice);
        }

        return compact(
            'baseCurrency',
            'selectedCurrency',
            'currencyConversion',
            'exchangeRate',
            'isThirdPartyInvoice',
            'invoiceMultiGeo',
            'restrictedTpScope'
        );
    }

    private function renderWithSampleData(
        string $name,
        string $html,
        string $css,
        string $type,
        string $outDir
    ): int {
        $cssSanitizer = new QuotationTemplateCssSanitizer();
        $renderer = new InvoiceTemplateRenderer(
            $cssSanitizer,
            new QuotationTemplateHtmlSanitizer($cssSanitizer)
        );

        $resolved = [
            'source' => InvoiceTemplateResolver::SOURCE_CUSTOM,
            'dmc_id' => 0,
            'invoice_type' => $type,
            'template_id' => null,
            'version_id' => null,
            'version_number' => null,
            'html_content' => $html,
            'css_content' => $css,
            'identifier' => 'draft:'.$name,
            'default_view' => InvoiceTemplateResolver::DEFAULT_VIEWS[$type]
                ?? InvoiceTemplateResolver::DEFAULT_VIEWS[InvoiceTemplate::TYPE_PROFORMA],
        ];

        try {
            $output = $renderer->render($resolved, $this->sampleInvoiceData($type));
        } catch (InvoiceTemplateRenderException $e) {
            $this->error('Draft failed validation: '.$e->getMessage());

            return self::FAILURE;
        }

        $outPath = $outDir.DIRECTORY_SEPARATOR.$name.'-preview.html';
        File::put($outPath, $output);

        $this->info('Draft rendered with sample data only.');
        $this->line('Preview: '.$outPath);
        $this->warn('For full invoice data, pass --invoice=INVOICE_ID (same payload as PDF download).');
        $this->comment('If the preview looks good, copy '.$name.'.html / '.$name.'.css into Invoice Template Studio and Save Draft.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleInvoiceData(string $type): array
    {
        return [
            'invoice' => [
                'id' => 1,
                'type' => str_starts_with($type, 'final') ? 'final' : 'proforma',
                'template_type' => $type,
                'status' => 'draft',
                'number' => 'DRAFT-INV-001',
                'invoice_number' => 'INV-001',
                'proforma_number' => 'PF-001',
                'invoice_date' => '2026-11-04',
                'due_date' => '2026-11-18',
                'validity_date' => null,
                'mode' => str_contains($type, 'price_only') ? 'price-only' : 'full',
            ],
            'branding' => [
                'mode' => 'dmc',
                'company_name' => 'Sample DMC',
                'logo' => null,
                'address' => '1 Sample Street',
                'email' => 'ops@sample.test',
                'phone' => '+65 0000 0000',
            ],
            'client' => [
                'lead_guest_name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => null,
            ],
            'travel_company' => [
                'name' => 'Sample Agency',
                'company_name' => 'Sample Agency',
            ],
            'travel' => [
                'destination' => 'Singapore',
                'from_date' => '2026-12-01',
                'to_date' => '2026-12-05',
                'duration_days' => 5,
                'adults' => 2,
                'children' => 0,
                'infants' => 0,
            ],
            'items' => [
                [
                    'id' => 1,
                    'item_type' => 'hotel',
                    'description' => 'Grand Hotel — Deluxe',
                    'quantity' => 1,
                    'unit_price' => 500,
                    'total_price' => 500,
                    'currency' => 'SGD',
                ],
                [
                    'id' => 2,
                    'item_type' => 'transfer',
                    'description' => 'Airport transfer',
                    'quantity' => 2,
                    'unit_price' => 40,
                    'total_price' => 80,
                    'currency' => 'SGD',
                ],
            ],
            'pricing' => [
                'base_currency' => 'SGD',
                'display_currency' => 'SGD',
                'exchange_rate' => 1.0,
                'currency_conversion' => [],
                'subtotal' => 580,
                'gst_amount' => 0,
                'service_charge' => 0,
                'tourist_tax' => 0,
                'total_amount' => 580,
                'payment_received' => 0,
                'outstanding_balance' => 580,
            ],
            'bank_details' => [
                'account_name' => 'Sample DMC',
                'account_number' => '000111',
            ],
            'payment_terms' => [],
            'terms_and_conditions' => 'Sample terms for draft checking only.',
            'notes' => [],
            'tour' => [
                'id' => 1,
                'status' => null,
            ],
        ];
    }
}
