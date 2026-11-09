<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\InvoiceTemplateVersion;
use App\Models\Tour;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\Invoices\InvoicePdfService;
use App\Services\Invoices\InvoiceTemplateDraftService;
use App\Services\Invoices\InvoiceTemplatePublishService;
use App\Services\Invoices\InvoiceTemplateRenderException;
use App\Services\Invoices\InvoiceTemplateRenderer;
use App\Services\Invoices\InvoiceTemplateResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class InvoiceTemplateStudioController extends Controller
{
    public function __construct(
        private readonly InvoiceTemplateDraftService $draftService,
        private readonly InvoiceTemplatePublishService $publishService,
        private readonly InvoicePdfService $invoicePdfService,
        private readonly InvoiceService $invoiceService
    ) {
    }

    public function index(Request $request): View
    {
        $this->assertSuperAdmin();

        $search = trim((string) $request->query('q', ''));

        $dmcsQuery = User::query()
            ->where('user_type', 2)
            ->where('role_id', 11)
            ->orderBy('name');

        if ($search !== '') {
            $dmcsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('userId', 'like', '%'.$search.'%');
            });
        }

        $dmcs = $dmcsQuery->get(['userId', 'name', 'email']);

        $templateSummary = InvoiceTemplate::query()
            ->whereIn('dmc_id', $dmcs->pluck('userId'))
            ->get(['dmc_id', 'invoice_type', 'active_version_id'])
            ->groupBy('dmc_id');

        return view('admin.invoice-templates.index', [
            'dmcs' => $dmcs,
            'search' => $search,
            'templateSummary' => $templateSummary,
        ]);
    }

    public function show(int $dmcId, string $type): View
    {
        $this->assertSuperAdmin();
        $this->assertValidTypeOrAbort($type);

        $dmc = $this->draftService->findDmcOrFail($dmcId);

        $template = InvoiceTemplate::query()
            ->where('dmc_id', $dmc->userId)
            ->where('invoice_type', $type)
            ->with([
                'activeVersion.createdBy',
                'versions' => function ($q) {
                    $q->orderByDesc('version')->with('createdBy');
                },
            ])
            ->first();

        $draft = null;
        $activePublished = null;
        $latestPublished = null;
        $isCustomActive = false;
        $editableHtml = '';
        $editableCss = '';
        $editableNotes = '';

        if ($template !== null) {
            $draft = $template->versions
                ->firstWhere('status', InvoiceTemplateVersion::STATUS_DRAFT);

            $activePublished = $template->activeVersion;
            $isCustomActive = $activePublished !== null
                && $activePublished->status === InvoiceTemplateVersion::STATUS_PUBLISHED;

            $latestPublished = $template->versions
                ->where('status', InvoiceTemplateVersion::STATUS_PUBLISHED)
                ->sortByDesc('version')
                ->first();

            if ($draft !== null) {
                $editableHtml = (string) $draft->html_content;
                $editableCss = (string) $draft->css_content;
                $editableNotes = (string) ($draft->change_notes ?? '');
            } elseif ($activePublished !== null) {
                // Seed editor from published content; saving creates a new draft.
                $editableHtml = (string) $activePublished->html_content;
                $editableCss = (string) $activePublished->css_content;
                $editableNotes = '';
            } elseif ($latestPublished !== null) {
                $editableHtml = (string) $latestPublished->html_content;
                $editableCss = (string) $latestPublished->css_content;
                $editableNotes = '';
            }
        }

        return view('admin.invoice-templates.show', [
            'dmc' => $dmc,
            'type' => $type,
            'template' => $template,
            'draft' => $draft,
            'activePublished' => $activePublished,
            'latestPublished' => $latestPublished,
            'isCustomActive' => $isCustomActive,
            'versions' => $template?->versions ?? collect(),
            'editableHtml' => $editableHtml,
            'editableCss' => $editableCss,
            'editableNotes' => $editableNotes,
            'twigDocs' => $this->twigDocumentation(),
            'types' => InvoiceTemplate::typeLabels(),
        ]);
    }

    public function store(Request $request, int $dmcId, string $type): RedirectResponse
    {
        $this->assertSuperAdmin();
        $this->assertValidTypeOrAbort($type);
        $this->draftService->findDmcOrFail($dmcId);

        $existing = InvoiceTemplate::query()
            ->where('dmc_id', $dmcId)
            ->where('invoice_type', $type)
            ->exists();

        if ($existing) {
            return redirect()
                ->route('invoice-templates.show', ['dmcId' => $dmcId, 'type' => $type])
                ->withErrors(['html_content' => 'A template already exists for this DMC and type. Use Save Draft instead.']);
        }

        return $this->persistDraft($request, $dmcId, $type, 'Template draft created.');
    }

    public function updateDraft(Request $request, int $dmcId, string $type): RedirectResponse
    {
        $this->assertSuperAdmin();
        $this->assertValidTypeOrAbort($type);
        $this->draftService->findDmcOrFail($dmcId);

        return $this->persistDraft($request, $dmcId, $type, 'Draft saved.');
    }

    /**
     * Publish the open draft so invoice PDF generation uses the custom template.
     */
    public function publishDraft(Request $request, int $dmcId, string $type): RedirectResponse
    {
        $this->assertSuperAdmin();
        $this->assertValidTypeOrAbort($type);
        $this->draftService->findDmcOrFail($dmcId);

        try {
            $published = $this->publishService->publishDraft($dmcId, $type);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'publish' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('invoice-templates.show', ['dmcId' => $dmcId, 'type' => $type])
            ->with('success', 'Published v'.$published->version.'. Invoice PDFs for this DMC now use this template.');
    }

    /**
     * Clear active_version_id so this DMC falls back to the system invoice PDF.
     */
    public function deactivate(Request $request, int $dmcId, string $type): RedirectResponse
    {
        $this->assertSuperAdmin();
        $this->assertValidTypeOrAbort($type);
        $this->draftService->findDmcOrFail($dmcId);

        try {
            $this->publishService->deactivate($dmcId, $type);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'activate' => $e->getMessage(),
            ]);
        }

        $redirect = $this->redirectAfterActivationToggle($request, $dmcId, $type);

        return $redirect->with('success', 'Custom template deactivated. This DMC now uses the system default invoice.');
    }

    /**
     * Set active_version_id to a published version (latest, or ?version=N).
     */
    public function activate(Request $request, int $dmcId, string $type): RedirectResponse
    {
        $this->assertSuperAdmin();
        $this->assertValidTypeOrAbort($type);
        $this->draftService->findDmcOrFail($dmcId);

        $versionNumber = $request->input('version');
        $versionNumber = ($versionNumber !== null && $versionNumber !== '')
            ? (int) $versionNumber
            : null;

        try {
            $activated = $this->publishService->activate($dmcId, $type, $versionNumber);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'activate' => $e->getMessage(),
            ]);
        }

        $redirect = $this->redirectAfterActivationToggle($request, $dmcId, $type, $activated->version);

        return $redirect->with(
            'success',
            'Custom template activated (v'.$activated->version.'). Invoice PDFs for this DMC now use this template.'
        );
    }

    public function showVersion(int $dmcId, string $type, int $version): View
    {
        $this->assertSuperAdmin();
        $this->assertValidTypeOrAbort($type);

        $dmc = $this->draftService->findDmcOrFail($dmcId);

        $template = InvoiceTemplate::query()
            ->where('dmc_id', $dmc->userId)
            ->where('invoice_type', $type)
            ->firstOrFail();

        $versionModel = InvoiceTemplateVersion::query()
            ->where('invoice_template_id', $template->id)
            ->where('version', $version)
            ->with('createdBy')
            ->firstOrFail();

        return view('admin.invoice-templates.version-show', [
            'dmc' => $dmc,
            'type' => $type,
            'template' => $template,
            'versionModel' => $versionModel,
            'isActiveVersion' => (int) ($template->active_version_id ?? 0) === (int) $versionModel->id,
            'types' => InvoiceTemplate::typeLabels(),
        ]);
    }

    private function redirectAfterActivationToggle(
        Request $request,
        int $dmcId,
        string $type,
        ?int $versionNumber = null
    ): RedirectResponse {
        if ($request->input('return') === 'index') {
            return redirect()->route('invoice-templates.index');
        }

        if ($request->input('return') === 'version' && $versionNumber !== null) {
            return redirect()->route('invoice-templates.versions.show', [
                'dmcId' => $dmcId,
                'type' => $type,
                'version' => $versionNumber,
            ]);
        }

        if ($request->input('return') === 'version' && $request->filled('version')) {
            return redirect()->route('invoice-templates.versions.show', [
                'dmcId' => $dmcId,
                'type' => $type,
                'version' => (int) $request->input('version'),
            ]);
        }

        return redirect()->route('invoice-templates.show', ['dmcId' => $dmcId, 'type' => $type]);
    }

    /**
     * Browser HTML preview of a local draft file against a real invoice (no DB save).
     */
    public function previewLocalDraft(Request $request, string $name): SymfonyResponse|RedirectResponse
    {
        return $this->renderLocalDraft($request, $name, false);
    }

    /**
     * DomPDF preview of a local draft file against a real invoice (no DB save).
     */
    public function previewLocalDraftPdf(Request $request, string $name): SymfonyResponse|RedirectResponse
    {
        return $this->renderLocalDraft($request, $name, true);
    }

    private function renderLocalDraft(Request $request, string $name, bool $asPdf): SymfonyResponse|RedirectResponse
    {
        $this->assertSuperAdmin();

        $name = basename($name);
        $validated = $request->validate([
            'tour_id' => ['nullable', 'integer', 'min:1'],
            'invoice_id' => ['nullable', 'integer', 'min:1'],
            'currency' => ['nullable', 'string', 'max:10'],
            'logo_type' => ['nullable', 'in:dmc,agency'],
        ]);

        if (empty($validated['tour_id']) && empty($validated['invoice_id'])) {
            return back()->withErrors([
                'tour_id' => 'Enter a Tour ID (preferred) or Invoice ID to preview.',
            ]);
        }

        $studioDmcId = $request->query('dmc_id');
        $studioDmcId = ($studioDmcId !== null && $studioDmcId !== '') ? (int) $studioDmcId : null;
        if ($studioDmcId !== null) {
            $this->draftService->findDmcOrFail($studioDmcId);
        }

        try {
            $invoice = $this->resolveInvoiceForDraftPreview(
                isset($validated['tour_id']) ? (int) $validated['tour_id'] : null,
                isset($validated['invoice_id']) ? (int) $validated['invoice_id'] : null,
                $studioDmcId
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['tour_id' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'tour_id' => 'Unable to resolve invoice for preview: '.$e->getMessage(),
            ]);
        }

        $draftName = $this->resolveDraftBasename($name, (string) $request->query('type', ''));
        $base = resource_path('invoice-templates/drafts/'.$draftName);
        $htmlPath = $base.'.html';
        $cssPath = $base.'.css';

        if (! File::isFile($htmlPath)) {
            abort(404, 'Draft HTML file not found: '.$draftName.'.html');
        }

        $html = File::get($htmlPath);
        $css = File::isFile($cssPath) ? File::get($cssPath) : '';
        $templateType = $this->resolveTemplateType($draftName, (string) $request->query('type', ''));
        $mode = $this->modeForType($templateType);
        $logoType = (string) ($validated['logo_type'] ?? 'dmc');
        $currencyQuery = isset($validated['currency']) ? strtoupper((string) $validated['currency']) : null;

        $currency = $this->buildInvoiceCurrencyContext($invoice, $currencyQuery);
        $defaultView = InvoiceTemplateResolver::DEFAULT_VIEWS[$templateType]
            ?? InvoiceTemplateResolver::DEFAULT_VIEWS[InvoiceTemplate::TYPE_PROFORMA];

        $viewData = array_merge($currency, [
            'invoice' => $invoice,
            'logoType' => $logoType,
            'mode' => $mode,
        ]);

        try {
            $response = $this->invoicePdfService->previewLocalDraft(
                $viewData,
                $html,
                $css,
                $templateType,
                $defaultView,
                $asPdf
            );
        } catch (InvoiceTemplateRenderException $e) {
            return back()->withErrors([
                'tour_id' => 'Draft render failed: '.$e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'tour_id' => 'Draft preview failed. Check the draft HTML/CSS and tour/invoice data.',
            ]);
        }

        return $response;
    }

    /**
     * Resolve invoice for Studio draft preview.
     * Prefers tour_id (same as quotation Studio). Ensures invoice exists for post-Prospect tours.
     *
     * @throws \InvalidArgumentException
     */
    private function resolveInvoiceForDraftPreview(?int $tourId, ?int $invoiceId, ?int $studioDmcId): Invoice
    {
        if ($tourId !== null && $tourId > 0) {
            $tour = Tour::query()->where('tour_id', $tourId)->first();
            if ($tour === null) {
                throw new \InvalidArgumentException('Tour not found for tour_id '.$tourId.'.');
            }

            if ($studioDmcId !== null && (int) ($tour->dmc_id ?? 0) !== $studioDmcId) {
                throw new \InvalidArgumentException(
                    'Tour '.$tourId.' does not belong to DMC '.$studioDmcId.'.'
                );
            }

            $status = (string) ($tour->tour_status ?? '');
            if (! in_array($status, InvoiceService::invoiceEligibleStatuses(), true)) {
                throw new \InvalidArgumentException(
                    'Invoice preview requires tour status Tentative, Confirmed, Definite, or Actual (after Prospect). Current status: '
                    .($status !== '' ? $status : 'N/A').'.'
                );
            }

            $invoice = $this->invoiceService->ensureInvoiceForTour($tourId);

            return Invoice::with(['tour', 'agent.agency', 'dmc', 'items'])
                ->where('invoice_id', $invoice->invoice_id)
                ->firstOrFail();
        }

        $invoice = Invoice::with(['tour', 'agent.agency', 'dmc', 'items'])
            ->where('invoice_id', $invoiceId)
            ->first();

        if ($invoice === null) {
            throw new \InvalidArgumentException('Invoice not found for invoice_id '.$invoiceId.'.');
        }

        if ($studioDmcId !== null && (int) ($invoice->dmc_id ?? 0) !== $studioDmcId) {
            throw new \InvalidArgumentException(
                'Invoice '.$invoiceId.' does not belong to DMC '.$studioDmcId.'.'
            );
        }

        return $invoice;
    }

    /**
     * Mirror InvoiceController::buildInvoiceCurrencyContext using CommonHelper.
     *
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

    private function resolveDraftBasename(string $name, string $type): string
    {
        if ($name !== '') {
            return $name;
        }

        return $this->draftBasenameForType($type !== '' ? $type : InvoiceTemplate::TYPE_PROFORMA);
    }

    private function resolveTemplateType(string $draftName, string $typeQuery): string
    {
        if ($typeQuery !== '' && in_array($typeQuery, InvoiceTemplate::types(), true)) {
            return $typeQuery;
        }

        return match ($draftName) {
            'proforma-price-only' => InvoiceTemplate::TYPE_PROFORMA_PRICE_ONLY,
            'final' => InvoiceTemplate::TYPE_FINAL,
            'final-price-only' => InvoiceTemplate::TYPE_FINAL_PRICE_ONLY,
            'alternate' => InvoiceTemplate::TYPE_ALTERNATE,
            default => InvoiceTemplate::TYPE_PROFORMA,
        };
    }

    private function draftBasenameForType(string $type): string
    {
        return match ($type) {
            InvoiceTemplate::TYPE_PROFORMA_PRICE_ONLY => 'proforma-price-only',
            InvoiceTemplate::TYPE_FINAL => 'final',
            InvoiceTemplate::TYPE_FINAL_PRICE_ONLY => 'final-price-only',
            InvoiceTemplate::TYPE_ALTERNATE => 'alternate',
            default => 'proforma',
        };
    }

    private function modeForType(string $type): string
    {
        return in_array($type, [
            InvoiceTemplate::TYPE_PROFORMA_PRICE_ONLY,
            InvoiceTemplate::TYPE_FINAL_PRICE_ONLY,
        ], true) ? 'price-only' : 'full';
    }

    private function persistDraft(Request $request, int $dmcId, string $type, string $successMessage): RedirectResponse
    {
        $validated = $request->validate([
            'html_content' => [
                'required',
                'string',
                'max:'.InvoiceTemplateDraftService::MAX_HTML_CHARS,
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! is_string($value) || trim($value) === '') {
                        $fail('The HTML content cannot be empty.');
                    }
                },
            ],
            'css_content' => [
                'nullable',
                'string',
                'max:'.InvoiceTemplateDraftService::MAX_CSS_CHARS,
            ],
            'change_notes' => [
                'nullable',
                'string',
                'max:'.InvoiceTemplateDraftService::MAX_CHANGE_NOTES_CHARS,
            ],
        ]);

        // Never trust client-supplied ownership or creator fields — path + Auth only.
        $admin = Auth::user();
        $createdBy = (int) ($admin->userId ?? 0);

        try {
            $this->draftService->saveDraft($dmcId, $type, $createdBy, [
                'html_content' => $validated['html_content'],
                'css_content' => $validated['css_content'] ?? '',
                'change_notes' => $validated['change_notes'] ?? null,
            ]);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'html_content' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('invoice-templates.show', ['dmcId' => $dmcId, 'type' => $type])
            ->with('success', $successMessage);
    }

    private function assertSuperAdmin(): void
    {
        $user = Auth::user();
        if (! $user || (int) ($user->user_type ?? 0) !== 1 || (int) ($user->role_id ?? 0) !== 1) {
            abort(403, 'Unauthorized.');
        }
    }

    private function assertValidTypeOrAbort(string $type): void
    {
        if (! in_array($type, InvoiceTemplate::types(), true)) {
            abort(404);
        }
    }

    /**
     * Documentation sourced from InvoiceTemplateRenderer.
     *
     * @return array{tags: list<string>, filters: list<string>, functions: list<string>, variables: list<string>}
     */
    private function twigDocumentation(): array
    {
        $ref = new ReflectionClass(InvoiceTemplateRenderer::class);

        $tags = $ref->getConstant('ALLOWED_TAGS') ?: ['if', 'for', 'set'];
        $filters = $ref->getConstant('ALLOWED_FILTERS') ?: [];
        $functions = $ref->getConstant('ALLOWED_FUNCTIONS') ?: ['min', 'max', 'range', 'date'];
        $arrayKeys = $ref->getConstant('ARRAY_DATA_KEYS') ?: [];
        $scalarKeys = $ref->getConstant('SCALAR_DATA_KEYS') ?: [];

        return [
            'tags' => is_array($tags) ? array_values($tags) : [],
            'filters' => is_array($filters) ? array_values($filters) : [],
            'functions' => is_array($functions) ? array_values($functions) : [],
            'variables' => array_values(array_merge(
                is_array($arrayKeys) ? $arrayKeys : [],
                is_array($scalarKeys) ? $scalarKeys : [],
                ['presentation', 'template']
            )),
        ];
    }
}
