<?php

namespace App\Services\Invoices;

use App\Models\InvoiceTemplate;
use App\Models\InvoiceTemplateVersion;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Publishes drafts and toggles whether a DMC's custom invoice template is live.
 */
class InvoiceTemplatePublishService
{
    public function __construct(
        private readonly InvoiceTemplateDraftService $draftService
    ) {
    }

    public function publishDraft(int $dmcId, string $invoiceType): InvoiceTemplateVersion
    {
        $this->draftService->assertInvoiceType($invoiceType);
        $this->draftService->findDmcOrFail($dmcId);

        return DB::transaction(function () use ($dmcId, $invoiceType) {
            $template = InvoiceTemplate::query()
                ->where('dmc_id', $dmcId)
                ->where('invoice_type', $invoiceType)
                ->lockForUpdate()
                ->first();

            if ($template === null) {
                throw new InvalidArgumentException('No template exists for this DMC and invoice type.');
            }

            $draft = InvoiceTemplateVersion::query()
                ->where('invoice_template_id', $template->id)
                ->where('status', InvoiceTemplateVersion::STATUS_DRAFT)
                ->orderByDesc('version')
                ->lockForUpdate()
                ->first();

            if ($draft === null) {
                throw new InvalidArgumentException('No open draft to publish. Save a draft first.');
            }

            if (trim((string) $draft->html_content) === '') {
                throw new InvalidArgumentException('Draft HTML content cannot be empty.');
            }

            InvoiceTemplateVersion::query()
                ->where('invoice_template_id', $template->id)
                ->where('status', InvoiceTemplateVersion::STATUS_PUBLISHED)
                ->where('id', '!=', $draft->id)
                ->update([
                    'status' => InvoiceTemplateVersion::STATUS_ARCHIVED,
                    'updated_at' => now(),
                ]);

            $draft->status = InvoiceTemplateVersion::STATUS_PUBLISHED;
            $draft->published_at = now();
            $draft->save();

            $template->active_version_id = $draft->id;
            $template->save();

            $published = $draft->fresh(['template']);
            if ($published === null
                || $published->status !== InvoiceTemplateVersion::STATUS_PUBLISHED
                || (int) $template->fresh()->active_version_id !== (int) $published->id
            ) {
                throw new RuntimeException('Publish did not activate the draft version.');
            }

            return $published;
        });
    }

    public function deactivate(int $dmcId, string $invoiceType): InvoiceTemplate
    {
        $this->draftService->assertInvoiceType($invoiceType);
        $this->draftService->findDmcOrFail($dmcId);

        return DB::transaction(function () use ($dmcId, $invoiceType) {
            $template = $this->lockTemplateOrFail($dmcId, $invoiceType);

            if (empty($template->active_version_id)) {
                throw new InvalidArgumentException('Custom template is already inactive for this DMC and type.');
            }

            $template->active_version_id = null;
            $template->save();

            return $template->fresh();
        });
    }

    public function activate(int $dmcId, string $invoiceType, ?int $versionNumber = null): InvoiceTemplateVersion
    {
        $this->draftService->assertInvoiceType($invoiceType);
        $this->draftService->findDmcOrFail($dmcId);

        return DB::transaction(function () use ($dmcId, $invoiceType, $versionNumber) {
            $template = $this->lockTemplateOrFail($dmcId, $invoiceType);

            $query = InvoiceTemplateVersion::query()
                ->where('invoice_template_id', $template->id)
                ->where('status', InvoiceTemplateVersion::STATUS_PUBLISHED)
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

    private function lockTemplateOrFail(int $dmcId, string $invoiceType): InvoiceTemplate
    {
        $template = InvoiceTemplate::query()
            ->where('dmc_id', $dmcId)
            ->where('invoice_type', $invoiceType)
            ->lockForUpdate()
            ->first();

        if ($template === null) {
            throw new InvalidArgumentException('No template exists for this DMC and invoice type.');
        }

        return $template;
    }
}
