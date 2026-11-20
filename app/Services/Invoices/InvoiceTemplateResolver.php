<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\InvoiceTemplateVersion;
use App\Models\User;

/**
 * Selects a DMC's published custom invoice template, or the system default Blade view.
 *
 * DMC ownership is taken only from the invoice's dmc() relationship (users.userId).
 * Request-supplied DMC IDs and agent IDs are never used.
 */
class InvoiceTemplateResolver
{
    public const SOURCE_CUSTOM = 'custom';

    public const SOURCE_SYSTEM = 'system';

    public const DEFAULT_VIEWS = [
        InvoiceTemplate::TYPE_PROFORMA => 'invoices.pdf.proforma',
        InvoiceTemplate::TYPE_PROFORMA_PRICE_ONLY => 'invoices.pdf.proforma-price-only',
        InvoiceTemplate::TYPE_FINAL => 'invoices.pdf.final',
        InvoiceTemplate::TYPE_FINAL_PRICE_ONLY => 'invoices.pdf.final-price-only',
        InvoiceTemplate::TYPE_ALTERNATE => 'invoices.pdf.alternate',
    ];

    /**
     * Map PDF axes (invoice_type × mode × format) to a template type key.
     */
    public static function typeFromAxes(string $invoiceType, string $mode, ?string $format): string
    {
        $format = $format ?: 'standard';
        if ($format === 'alternate') {
            return InvoiceTemplate::TYPE_ALTERNATE;
        }

        if (! in_array($mode, ['full', 'price-only'], true)) {
            $mode = 'full';
        }

        $isProforma = $invoiceType === 'proforma';

        if ($mode === 'price-only') {
            return $isProforma
                ? InvoiceTemplate::TYPE_PROFORMA_PRICE_ONLY
                : InvoiceTemplate::TYPE_FINAL_PRICE_ONLY;
        }

        return $isProforma
            ? InvoiceTemplate::TYPE_PROFORMA
            : InvoiceTemplate::TYPE_FINAL;
    }

    /**
     * @return array{
     *     source: string,
     *     dmc_id: int|null,
     *     invoice_type: string,
     *     template_id: int|null,
     *     version_id: int|null,
     *     version_number: int|null,
     *     html_content: string|null,
     *     css_content: string|null,
     *     identifier: string,
     *     default_view: string
     * }
     */
    public function resolve(Invoice $invoice, string $invoiceTemplateType): array
    {
        $invoiceTemplateType = $this->assertSupportedType($invoiceTemplateType);
        $dmcId = $this->resolveDmcId($invoice);

        if ($dmcId === null) {
            return $this->systemResult($invoiceTemplateType, null);
        }

        $template = InvoiceTemplate::query()
            ->where('dmc_id', $dmcId)
            ->where('invoice_type', $invoiceTemplateType)
            ->first();

        if ($template === null || empty($template->active_version_id)) {
            return $this->systemResult($invoiceTemplateType, $dmcId);
        }

        $version = InvoiceTemplateVersion::query()->find($template->active_version_id);
        if (! $this->isUsablePublishedVersion($template, $version)) {
            return $this->systemResult($invoiceTemplateType, $dmcId);
        }

        return [
            'source' => self::SOURCE_CUSTOM,
            'dmc_id' => $dmcId,
            'invoice_type' => $invoiceTemplateType,
            'template_id' => (int) $template->id,
            'version_id' => (int) $version->id,
            'version_number' => (int) $version->version,
            'html_content' => (string) $version->html_content,
            'css_content' => (string) ($version->css_content ?? ''),
            'identifier' => $this->identifier($dmcId, $invoiceTemplateType, (int) $version->id),
            'default_view' => self::DEFAULT_VIEWS[$invoiceTemplateType],
        ];
    }

    private function assertSupportedType(string $invoiceTemplateType): string
    {
        $invoiceTemplateType = strtolower(trim($invoiceTemplateType));

        if (! in_array($invoiceTemplateType, InvoiceTemplate::types(), true)) {
            throw new UnsupportedInvoiceTypeException(
                'Unsupported invoice template type ['.$invoiceTemplateType.'].'
            );
        }

        return $invoiceTemplateType;
    }

    private function resolveDmcId(Invoice $invoice): ?int
    {
        $dmc = $invoice->dmc;
        if (! $dmc instanceof User) {
            $dmcId = (int) ($invoice->dmc_id ?? 0);

            return $dmcId > 0 ? $dmcId : null;
        }

        $dmcId = (int) ($dmc->userId ?? 0);

        return $dmcId > 0 ? $dmcId : null;
    }

    private function isUsablePublishedVersion(InvoiceTemplate $template, mixed $version): bool
    {
        if (! $version instanceof InvoiceTemplateVersion) {
            return false;
        }

        if ((int) $version->invoice_template_id !== (int) $template->id) {
            return false;
        }

        if ($version->status !== InvoiceTemplateVersion::STATUS_PUBLISHED) {
            return false;
        }

        return trim((string) $version->html_content) !== '';
    }

    /**
     * @return array{
     *     source: string,
     *     dmc_id: int|null,
     *     invoice_type: string,
     *     template_id: int|null,
     *     version_id: int|null,
     *     version_number: int|null,
     *     html_content: string|null,
     *     css_content: string|null,
     *     identifier: string,
     *     default_view: string
     * }
     */
    private function systemResult(string $invoiceTemplateType, ?int $dmcId): array
    {
        $dmcKey = $dmcId !== null ? (string) $dmcId : 'none';

        return [
            'source' => self::SOURCE_SYSTEM,
            'dmc_id' => $dmcId,
            'invoice_type' => $invoiceTemplateType,
            'template_id' => null,
            'version_id' => null,
            'version_number' => null,
            'html_content' => null,
            'css_content' => null,
            'identifier' => 'invoice-template:'.$dmcKey.':'.$invoiceTemplateType.':system',
            'default_view' => self::DEFAULT_VIEWS[$invoiceTemplateType],
        ];
    }

    private function identifier(int $dmcId, string $invoiceTemplateType, int $versionId): string
    {
        return 'invoice-template:'.$dmcId.':'.$invoiceTemplateType.':'.$versionId;
    }
}
