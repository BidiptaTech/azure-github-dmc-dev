<?php

namespace App\Services\Quotations;

use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateVersion;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Publishes drafts and toggles whether a DMC's custom template is live.
 *
 * - Publish: draft → published, set active_version_id, archive prior published.
 * - Deactivate: clear active_version_id (PDF falls back to system Blade).
 * - Activate: point active_version_id at a published version again.
 */
class QuotationTemplatePublishService
{
    public function __construct(
        private readonly QuotationTemplateDraftService $draftService
    ) {
    }

    /**
     * Publish the current open draft for DMC + quotation type.
     */
    public function publishDraft(int $dmcId, string $quotationType): QuotationTemplateVersion
    {
        $this->draftService->assertQuotationType($quotationType);
        $this->draftService->findDmcOrFail($dmcId);

        return DB::transaction(function () use ($dmcId, $quotationType) {
            $template = QuotationTemplate::query()
                ->where('dmc_id', $dmcId)
                ->where('quotation_type', $quotationType)
                ->lockForUpdate()
                ->first();

            if ($template === null) {
                throw new InvalidArgumentException('No template exists for this DMC and quotation type.');
            }

            $draft = QuotationTemplateVersion::query()
                ->where('quotation_template_id', $template->id)
                ->where('status', QuotationTemplateVersion::STATUS_DRAFT)
                ->orderByDesc('version')
                ->lockForUpdate()
                ->first();

            if ($draft === null) {
                throw new InvalidArgumentException('No open draft to publish. Save a draft first.');
            }

            if (trim((string) $draft->html_content) === '') {
                throw new InvalidArgumentException('Draft HTML content cannot be empty.');
            }

            QuotationTemplateVersion::query()
                ->where('quotation_template_id', $template->id)
                ->where('status', QuotationTemplateVersion::STATUS_PUBLISHED)
                ->where('id', '!=', $draft->id)
                ->update([
                    'status' => QuotationTemplateVersion::STATUS_ARCHIVED,
                    'updated_at' => now(),
                ]);

            $draft->status = QuotationTemplateVersion::STATUS_PUBLISHED;
            $draft->published_at = now();
            $draft->save();

            $template->active_version_id = $draft->id;
            $template->save();

            $published = $draft->fresh(['template']);
            if ($published === null
                || $published->status !== QuotationTemplateVersion::STATUS_PUBLISHED
                || (int) $template->fresh()->active_version_id !== (int) $published->id
            ) {
                throw new RuntimeException('Publish did not activate the draft version.');
            }

            return $published;
        });
    }

    /**
     * Clear active_version_id so production PDFs use the system default.
     * Published version rows are left intact for later reactivation.
     */
    public function deactivate(int $dmcId, string $quotationType): QuotationTemplate
    {
        $this->draftService->assertQuotationType($quotationType);
        $this->draftService->findDmcOrFail($dmcId);

        return DB::transaction(function () use ($dmcId, $quotationType) {
            $template = $this->lockTemplateOrFail($dmcId, $quotationType);

            if (empty($template->active_version_id)) {
                throw new InvalidArgumentException('Custom template is already inactive for this DMC and type.');
            }

            $template->active_version_id = null;
            $template->save();

            return $template->fresh();
        });
    }

    /**
     * Point active_version_id at a published version so custom PDFs are used again.
     *
     * @param  int|null  $versionNumber  Specific published version number, or null for latest published.
     */
    public function activate(int $dmcId, string $quotationType, ?int $versionNumber = null): QuotationTemplateVersion
    {
        $this->draftService->assertQuotationType($quotationType);
        $this->draftService->findDmcOrFail($dmcId);

        return DB::transaction(function () use ($dmcId, $quotationType, $versionNumber) {
            $template = $this->lockTemplateOrFail($dmcId, $quotationType);

            $query = QuotationTemplateVersion::query()
                ->where('quotation_template_id', $template->id)
                ->where('status', QuotationTemplateVersion::STATUS_PUBLISHED)
                ->lockForUpdate();

            if ($versionNumber !== null) {
                $query->where('version', $versionNumber);
            } else {
                $query->orderByDesc('version');
            }

            $version = $query->first();

            if ($version === null) {
                throw new InvalidArgumentException(
                    $versionNumber !== null
                        ? 'Published version v'.$versionNumber.' was not found.'
                        : 'No published version available to activate. Publish a draft first.'
                );
            }

            if (trim((string) $version->html_content) === '') {
                throw new InvalidArgumentException('Cannot activate a published version with empty HTML.');
            }

            if ((int) $template->active_version_id === (int) $version->id) {
                throw new InvalidArgumentException('This version is already the active custom template.');
            }

            $template->active_version_id = $version->id;
            $template->save();

            $activated = $version->fresh(['template']);
            if ($activated === null || (int) $template->fresh()->active_version_id !== (int) $activated->id) {
                throw new RuntimeException('Activate did not set active_version_id.');
            }

            return $activated;
        });
    }

    private function lockTemplateOrFail(int $dmcId, string $quotationType): QuotationTemplate
    {
        $template = QuotationTemplate::query()
            ->where('dmc_id', $dmcId)
            ->where('quotation_type', $quotationType)
            ->lockForUpdate()
            ->first();

        if ($template === null) {
            throw new InvalidArgumentException('No template exists for this DMC and quotation type.');
        }

        return $template;
    }
}
