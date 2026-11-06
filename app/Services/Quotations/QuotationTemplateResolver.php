<?php

namespace App\Services\Quotations;

use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateVersion;
use App\Models\Tour;
use App\Models\User;

/**
 * Selects a DMC's published custom quotation template, or the system default.
 *
 * DMC ownership is taken only from the authorized tour's dmc() relationship
 * (users.userId). Request-supplied DMC IDs and agent IDs are never used.
 */
class QuotationTemplateResolver
{
    public const SOURCE_CUSTOM = 'custom';
    public const SOURCE_SYSTEM = 'system';

    public const DEFAULT_VIEWS = [
        QuotationTemplate::TYPE_ITINERARY => 'single-tour-package.quotation',
        QuotationTemplate::TYPE_DETAILED => 'single-tour-package.detailedqutation',
    ];

    /**
     * @return array{
     *     source: string,
     *     dmc_id: int|null,
     *     quotation_type: string,
     *     template_id: int|null,
     *     version_id: int|null,
     *     version_number: int|null,
     *     html_content: string|null,
     *     css_content: string|null,
     *     identifier: string,
     *     default_view: string
     * }
     */
    public function resolve(Tour $tour, string $quotationType): array
    {
        $quotationType = $this->assertSupportedType($quotationType);
        $dmcId = $this->resolveDmcId($tour);

        if ($dmcId === null) {
            return $this->systemResult($quotationType, null);
        }

        $template = QuotationTemplate::query()
            ->where('dmc_id', $dmcId)
            ->where('quotation_type', $quotationType)
            ->first();

        if ($template === null || empty($template->active_version_id)) {
            return $this->systemResult($quotationType, $dmcId);
        }

        $version = QuotationTemplateVersion::query()->find($template->active_version_id);
        if (! $this->isUsablePublishedVersion($template, $version)) {
            return $this->systemResult($quotationType, $dmcId);
        }

        return [
            'source' => self::SOURCE_CUSTOM,
            'dmc_id' => $dmcId,
            'quotation_type' => $quotationType,
            'template_id' => (int) $template->id,
            'version_id' => (int) $version->id,
            'version_number' => (int) $version->version,
            'html_content' => (string) $version->html_content,
            'css_content' => (string) ($version->css_content ?? ''),
            'identifier' => $this->identifier($dmcId, $quotationType, (int) $version->id),
            'default_view' => self::DEFAULT_VIEWS[$quotationType],
        ];
    }

    private function assertSupportedType(string $quotationType): string
    {
        $quotationType = strtolower(trim($quotationType));

        if (! in_array($quotationType, [QuotationTemplate::TYPE_ITINERARY, QuotationTemplate::TYPE_DETAILED], true)) {
            throw new UnsupportedQuotationTypeException(
                'Unsupported quotation type ['.$quotationType.']. Expected '
                .QuotationTemplate::TYPE_ITINERARY.' or '.QuotationTemplate::TYPE_DETAILED.'.'
            );
        }

        return $quotationType;
    }

    private function resolveDmcId(Tour $tour): ?int
    {
        $dmc = $tour->dmc;
        if (! $dmc instanceof User) {
            return null;
        }

        $dmcId = (int) ($dmc->userId ?? 0);

        return $dmcId > 0 ? $dmcId : null;
    }

    private function isUsablePublishedVersion(QuotationTemplate $template, mixed $version): bool
    {
        if (! $version instanceof QuotationTemplateVersion) {
            return false;
        }

        if ((int) $version->quotation_template_id !== (int) $template->id) {
            return false;
        }

        if ($version->status !== QuotationTemplateVersion::STATUS_PUBLISHED) {
            return false;
        }

        return trim((string) $version->html_content) !== '';
    }

    /**
     * @return array{
     *     source: string,
     *     dmc_id: int|null,
     *     quotation_type: string,
     *     template_id: int|null,
     *     version_id: int|null,
     *     version_number: int|null,
     *     html_content: string|null,
     *     css_content: string|null,
     *     identifier: string,
     *     default_view: string
     * }
     */
    private function systemResult(string $quotationType, ?int $dmcId): array
    {
        $dmcKey = $dmcId !== null ? (string) $dmcId : 'none';

        return [
            'source' => self::SOURCE_SYSTEM,
            'dmc_id' => $dmcId,
            'quotation_type' => $quotationType,
            'template_id' => null,
            'version_id' => null,
            'version_number' => null,
            'html_content' => null,
            'css_content' => null,
            'identifier' => 'quotation-template:'.$dmcKey.':'.$quotationType.':system',
            'default_view' => self::DEFAULT_VIEWS[$quotationType],
        ];
    }

    private function identifier(int $dmcId, string $quotationType, int $versionId): string
    {
        return 'quotation-template:'.$dmcId.':'.$quotationType.':'.$versionId;
    }
}
