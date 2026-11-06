<?php

namespace App\Services\Invoices;

use App\Models\InvoiceTemplate;
use App\Models\InvoiceTemplateVersion;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Creates and updates draft invoice template versions for Super Admin Studio.
 *
 * Does not publish, switch active_version_id, or execute template content.
 */
class InvoiceTemplateDraftService
{
    public const MAX_HTML_CHARS = 500_000;

    public const MAX_CSS_CHARS = 200_000;

    public const MAX_CHANGE_NOTES_CHARS = 2000;

    /**
     * @param  array{html_content: string, css_content?: string|null, change_notes?: string|null}  $payload
     */
    public function saveDraft(int $dmcId, string $invoiceType, int $createdByUserId, array $payload, int $attempt = 1): InvoiceTemplateVersion
    {
        $this->assertInvoiceType($invoiceType);
        $this->assertDmcUser($dmcId);
        $this->assertCreator($createdByUserId);

        $html = $this->normalizeHtml($payload['html_content'] ?? null);
        $css = $this->normalizeCss($payload['css_content'] ?? null);
        $notes = $this->normalizeChangeNotes($payload['change_notes'] ?? null);

        try {
            return DB::transaction(function () use ($dmcId, $invoiceType, $createdByUserId, $html, $css, $notes) {
                $template = InvoiceTemplate::query()
                    ->where('dmc_id', $dmcId)
                    ->where('invoice_type', $invoiceType)
                    ->lockForUpdate()
                    ->first();

                if ($template === null) {
                    $template = InvoiceTemplate::query()->create([
                        'dmc_id' => $dmcId,
                        'invoice_type' => $invoiceType,
                        'active_version_id' => null,
                    ]);

                    $template = InvoiceTemplate::query()
                        ->whereKey($template->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    return $this->createVersion($template, 1, $html, $css, $notes, $createdByUserId);
                }

                $activeVersionIdBefore = $template->active_version_id;

                $draft = InvoiceTemplateVersion::query()
                    ->where('invoice_template_id', $template->id)
                    ->where('status', InvoiceTemplateVersion::STATUS_DRAFT)
                    ->orderByDesc('version')
                    ->lockForUpdate()
                    ->first();

                if ($draft !== null) {
                    $draft->html_content = $html;
                    $draft->css_content = $css;
                    $draft->change_notes = $notes;
                    $draft->save();

                    $template->refresh();
                    if ($template->active_version_id !== $activeVersionIdBefore) {
                        throw new RuntimeException('Draft save must not change active_version_id.');
                    }

                    return $draft->fresh();
                }

                $nextVersion = (int) InvoiceTemplateVersion::query()
                    ->where('invoice_template_id', $template->id)
                    ->max('version');
                $nextVersion = $nextVersion > 0 ? $nextVersion + 1 : 1;

                $version = $this->createVersion($template, $nextVersion, $html, $css, $notes, $createdByUserId);

                $template->refresh();
                if ($template->active_version_id !== $activeVersionIdBefore) {
                    throw new RuntimeException('Draft save must not change active_version_id.');
                }

                return $version;
            });
        } catch (QueryException $e) {
            if ($attempt < 3 && $this->isUniqueConstraintViolation($e)) {
                return $this->saveDraft($dmcId, $invoiceType, $createdByUserId, [
                    'html_content' => $html,
                    'css_content' => $css,
                    'change_notes' => $notes,
                ], $attempt + 1);
            }

            throw $e;
        }
    }

    public function assertInvoiceType(string $invoiceType): void
    {
        if (! in_array($invoiceType, InvoiceTemplate::types(), true)) {
            throw new InvalidArgumentException('Invalid invoice type.');
        }
    }

    public function findDmcOrFail(int $dmcId): User
    {
        $dmc = User::query()
            ->where('userId', $dmcId)
            ->where('user_type', 2)
            ->where('role_id', 11)
            ->first();

        if ($dmc === null) {
            abort(404, 'DMC not found.');
        }

        return $dmc;
    }

    private function assertDmcUser(int $dmcId): void
    {
        $this->findDmcOrFail($dmcId);
    }

    private function assertCreator(int $createdByUserId): void
    {
        if ($createdByUserId < 1) {
            throw new InvalidArgumentException('Invalid creator.');
        }
    }

    private function normalizeHtml(mixed $html): string
    {
        if (! is_string($html)) {
            throw new InvalidArgumentException('HTML content is required.');
        }

        $trimmed = trim($html);
        if ($trimmed === '') {
            throw new InvalidArgumentException('HTML content cannot be empty.');
        }

        if (mb_strlen($html) > self::MAX_HTML_CHARS) {
            throw new InvalidArgumentException('HTML content exceeds the maximum allowed size.');
        }

        return $html;
    }

    private function normalizeCss(mixed $css): string
    {
        if ($css === null) {
            return '';
        }

        if (! is_string($css)) {
            throw new InvalidArgumentException('CSS content must be a string.');
        }

        if (mb_strlen($css) > self::MAX_CSS_CHARS) {
            throw new InvalidArgumentException('CSS content exceeds the maximum allowed size.');
        }

        return $css;
    }

    private function normalizeChangeNotes(mixed $notes): ?string
    {
        if ($notes === null || $notes === '') {
            return null;
        }

        if (! is_string($notes)) {
            throw new InvalidArgumentException('Change notes must be a string.');
        }

        if (mb_strlen($notes) > self::MAX_CHANGE_NOTES_CHARS) {
            throw new InvalidArgumentException('Change notes exceed the maximum allowed size.');
        }

        return $notes;
    }

    private function createVersion(
        InvoiceTemplate $template,
        int $versionNumber,
        string $html,
        string $css,
        ?string $notes,
        int $createdByUserId
    ): InvoiceTemplateVersion {
        return InvoiceTemplateVersion::query()->create([
            'invoice_template_id' => $template->id,
            'version' => $versionNumber,
            'html_content' => $html,
            'css_content' => $css,
            'status' => InvoiceTemplateVersion::STATUS_DRAFT,
            'change_notes' => $notes,
            'created_by' => $createdByUserId,
            'published_at' => null,
        ]);
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? '';
        $driverCode = (int) ($e->errorInfo[1] ?? 0);
        $message = strtolower($e->getMessage());

        return $sqlState === '23000'
            || $sqlState === '23505'
            || $driverCode === 1062
            || str_contains($message, 'unique')
            || str_contains($message, 'duplicate');
    }
}
