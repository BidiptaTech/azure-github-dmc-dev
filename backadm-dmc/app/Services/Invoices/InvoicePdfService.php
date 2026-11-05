<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

/**
 * Renders invoice PDFs via custom Twig templates or system Blade fallback.
 */
class InvoicePdfService
{
    /**
     * Request-scoped local draft override (Studio / artisan only).
     *
     * @var array{html_content: string, css_content: string, as_pdf?: bool}|null
     */
    protected static ?array $localDraftTemplateOverride = null;

    public function __construct(
        private readonly InvoiceDataBuilder $dataBuilder,
        private readonly InvoiceTemplateResolver $resolver,
        private readonly InvoiceTemplateRenderer $renderer
    ) {
    }

    /**
     * Preview a local HTML/CSS draft against a real invoice (no DB template read/write).
     *
     * @param  array<string, mixed>  $viewData  Must include invoice + currency/logo/mode
     * @return \Illuminate\Http\Response
     */
    public function previewLocalDraft(
        array $viewData,
        string $htmlContent,
        string $cssContent,
        string $invoiceTemplateType,
        string $defaultView,
        bool $asPdf = false
    ) {
        self::$localDraftTemplateOverride = [
            'html_content' => $htmlContent,
            'css_content' => $cssContent,
            'as_pdf' => $asPdf,
        ];

        try {
            return $this->streamOrDownload($viewData, $invoiceTemplateType, $defaultView, true, null);
        } finally {
            self::$localDraftTemplateOverride = null;
        }
    }

    /**
     * @param  array<string, mixed>  $viewData
     * @return \Illuminate\Http\Response
     */
    public function streamOrDownload(
        array $viewData,
        string $invoiceTemplateType,
        string $defaultView,
        bool $preview,
        ?string $downloadFilename = null
    ) {
        $viewData = $this->dataBuilder->forViewData($viewData, $invoiceTemplateType);
        $invoice = $viewData['invoice'] ?? null;
        $pdf = null;
        $resolvedDefaultView = $defaultView;

        if (is_array(self::$localDraftTemplateOverride)) {
            $invoiceData = is_array($viewData['invoiceData'] ?? null) ? $viewData['invoiceData'] : [];
            $draftResolved = [
                'source' => InvoiceTemplateResolver::SOURCE_CUSTOM,
                'dmc_id' => ($invoice instanceof Invoice) ? (int) ($invoice->dmc_id ?? 0) : 0,
                'invoice_type' => $invoiceTemplateType,
                'template_id' => null,
                'version_id' => null,
                'version_number' => null,
                'html_content' => (string) (self::$localDraftTemplateOverride['html_content'] ?? ''),
                'css_content' => (string) (self::$localDraftTemplateOverride['css_content'] ?? ''),
                'identifier' => 'local-draft:'.$invoiceTemplateType,
                'default_view' => $defaultView,
            ];

            $html = $this->renderer->render(
                $draftResolved,
                $invoiceData,
                $this->approvedPresentation($viewData)
            );

            if (empty(self::$localDraftTemplateOverride['as_pdf'])) {
                return response($html, 200, [
                    'Content-Type' => 'text/html; charset=UTF-8',
                    'X-Invoice-Template-Draft' => '1',
                ]);
            }

            $pdf = Pdf::loadHTML($html);

            return $this->finalize($pdf, $preview, $downloadFilename);
        }

        if ($invoice instanceof Invoice) {
            $resolved = $this->resolver->resolve($invoice, $invoiceTemplateType);
            $resolvedDefaultView = (string) ($resolved['default_view'] ?? $defaultView);

            if (($resolved['source'] ?? null) === InvoiceTemplateResolver::SOURCE_CUSTOM) {
                try {
                    $invoiceData = is_array($viewData['invoiceData'] ?? null) ? $viewData['invoiceData'] : [];
                    $html = $this->renderer->render(
                        $resolved,
                        $invoiceData,
                        $this->approvedPresentation($viewData)
                    );
                    $pdf = Pdf::loadHTML($html);
                } catch (SystemInvoiceTemplateFallbackException|InvoiceTemplateRenderException $exception) {
                    Log::warning('Custom invoice template render failed; using system Blade view', [
                        'invoice_id' => $invoice->invoice_id ?? null,
                        'invoice_type' => $invoiceTemplateType,
                        'template_identifier' => $resolved['identifier'] ?? null,
                        'template_version_id' => $resolved['version_id'] ?? null,
                        'exception' => $exception::class,
                        'message' => $exception->getMessage(),
                    ]);
                    $pdf = null;
                }
            }
        }

        if ($pdf === null) {
            $pdf = Pdf::loadView($resolvedDefaultView, $viewData);
        }

        return $this->finalize($pdf, $preview, $downloadFilename);
    }

    /**
     * @param  array<string, mixed>  $viewData
     * @return array<string, mixed>
     */
    protected function approvedPresentation(array $viewData): array
    {
        $approved = [];

        if (isset($viewData['logoType']) && is_string($viewData['logoType'])) {
            $approved['logoType'] = $viewData['logoType'];
        }

        if (isset($viewData['mode']) && is_string($viewData['mode'])) {
            $approved['mode'] = $viewData['mode'];
        }

        if (isset($viewData['selectedCurrency']) && (is_string($viewData['selectedCurrency']) || is_numeric($viewData['selectedCurrency']))) {
            $approved['selectedCurrency'] = $viewData['selectedCurrency'];
        }

        if (isset($viewData['baseCurrency']) && (is_string($viewData['baseCurrency']) || is_numeric($viewData['baseCurrency']))) {
            $approved['baseCurrency'] = $viewData['baseCurrency'];
        }

        if (isset($viewData['exchangeRate']) && (is_int($viewData['exchangeRate']) || is_float($viewData['exchangeRate']))) {
            $approved['exchangeRate'] = $viewData['exchangeRate'];
        }

        return $approved;
    }

    /**
     * @param  \Barryvdh\DomPDF\PDF  $pdf
     * @return \Illuminate\Http\Response
     */
    protected function finalize($pdf, bool $preview, ?string $downloadFilename)
    {
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-php', false);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);

        $filename = $downloadFilename ?: 'invoice.pdf';

        if ($preview) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}
