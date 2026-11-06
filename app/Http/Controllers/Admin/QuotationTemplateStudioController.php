<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateVersion;
use App\Models\Tour;
use App\Models\User;
use App\Services\Quotations\QuotationTemplateDraftService;
use App\Services\Quotations\QuotationTemplatePublishService;
use App\Services\Quotations\QuotationTemplateRenderException;
use App\Services\Quotations\QuotationTemplateRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class QuotationTemplateStudioController extends Controller
{
    public function __construct(
        private readonly QuotationTemplateDraftService $draftService,
        private readonly QuotationTemplatePublishService $publishService
    ) {
    }

    public function index(Request $request): View
    {
        $this->assertSuperAdmin();

        $search = trim((string) $request->query('q', ''));

        $dmcsQuery = User::query()
            ->where('user_type', 2)
            ->where('role_id', 11)
            ->orderBy('company_name');

        if ($search !== '') {
            $dmcsQuery->where(function ($q) use ($search) {
                $q->where('company_name', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('country', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('userId', 'like', '%'.$search.'%');
            });
        }

        $dmcs = $dmcsQuery->get(['userId', 'name', 'company_name', 'country', 'email']);

        $templateSummary = QuotationTemplate::query()
            ->whereIn('dmc_id', $dmcs->pluck('userId'))
            ->get(['dmc_id', 'quotation_type', 'active_version_id'])
            ->groupBy('dmc_id');

        return view('admin.quotation-templates.index', [
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

        $template = QuotationTemplate::query()
            ->where('dmc_id', $dmc->userId)
            ->where('quotation_type', $type)
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
                ->firstWhere('status', QuotationTemplateVersion::STATUS_DRAFT);

            $activePublished = $template->activeVersion;
            $isCustomActive = $activePublished !== null
                && $activePublished->status === QuotationTemplateVersion::STATUS_PUBLISHED;

            $latestPublished = $template->versions
                ->where('status', QuotationTemplateVersion::STATUS_PUBLISHED)
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

        return view('admin.quotation-templates.show', [
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
            'types' => [
                QuotationTemplate::TYPE_ITINERARY => 'Acco+Service Quotation',
                QuotationTemplate::TYPE_DETAILED => 'Detailed Quotation',
            ],
        ]);
    }

    public function store(Request $request, int $dmcId, string $type): RedirectResponse
    {
        $this->assertSuperAdmin();
        $this->assertValidTypeOrAbort($type);
        $this->draftService->findDmcOrFail($dmcId);

        $existing = QuotationTemplate::query()
            ->where('dmc_id', $dmcId)
            ->where('quotation_type', $type)
            ->exists();

        if ($existing) {
            return redirect()
                ->route('quotation-templates.show', ['dmcId' => $dmcId, 'type' => $type])
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
     * Publish the open draft so enquiry/PDF generation uses the custom template.
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
            ->route('quotation-templates.show', ['dmcId' => $dmcId, 'type' => $type])
            ->with('success', 'Published v'.$published->version.'. Acco+Service / Packaged quotation PDFs for this DMC now use this template.');
    }

    /**
     * Clear active_version_id so this DMC falls back to the system quotation PDF.
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

        return $redirect->with('success', 'Custom template deactivated. This DMC now uses the system default quotation.');
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
            'Custom template activated (v'.$activated->version.'). Enquiry quotation PDFs for this DMC now use this template.'
        );
    }

    public function showVersion(int $dmcId, string $type, int $version): View
    {
        $this->assertSuperAdmin();
        $this->assertValidTypeOrAbort($type);

        $dmc = $this->draftService->findDmcOrFail($dmcId);

        $template = QuotationTemplate::query()
            ->where('dmc_id', $dmc->userId)
            ->where('quotation_type', $type)
            ->firstOrFail();

        $versionModel = QuotationTemplateVersion::query()
            ->where('quotation_template_id', $template->id)
            ->where('version', $version)
            ->with('createdBy')
            ->firstOrFail();

        return view('admin.quotation-templates.version-show', [
            'dmc' => $dmc,
            'type' => $type,
            'template' => $template,
            'versionModel' => $versionModel,
            'isActiveVersion' => (int) ($template->active_version_id ?? 0) === (int) $versionModel->id,
            'types' => [
                QuotationTemplate::TYPE_ITINERARY => 'Acco+Service Quotation',
                QuotationTemplate::TYPE_DETAILED => 'Detailed Quotation',
            ],
        ]);
    }

    private function redirectAfterActivationToggle(
        Request $request,
        int $dmcId,
        string $type,
        ?int $versionNumber = null
    ): RedirectResponse {
        if ($request->input('return') === 'index') {
            return redirect()->route('quotation-templates.index');
        }

        if ($request->input('return') === 'version' && $versionNumber !== null) {
            return redirect()->route('quotation-templates.versions.show', [
                'dmcId' => $dmcId,
                'type' => $type,
                'version' => $versionNumber,
            ]);
        }

        if ($request->input('return') === 'version' && $request->filled('version')) {
            return redirect()->route('quotation-templates.versions.show', [
                'dmcId' => $dmcId,
                'type' => $type,
                'version' => (int) $request->input('version'),
            ]);
        }

        return redirect()->route('quotation-templates.show', ['dmcId' => $dmcId, 'type' => $type]);
    }

    /**
     * Browser HTML preview of a local draft file against a real tour (no DB save).
     */
    public function previewLocalDraft(Request $request, string $name): SymfonyResponse|RedirectResponse
    {
        return $this->renderLocalDraft($request, $name, false);
    }

    /**
     * DomPDF preview of a local draft file against a real tour (no DB save).
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
            'tour_id' => ['required', 'integer', 'min:1'],
            'currency' => ['nullable', 'string', 'max:10'],
            'logo_type' => ['nullable', 'in:dmc,agency'],
        ]);

        $tourId = (int) $validated['tour_id'];
        $tour = Tour::query()->where('tour_id', $tourId)->first();
        if ($tour === null) {
            return back()->withErrors(['tour_id' => 'Tour not found for tour_id '.$tourId.'.']);
        }

        // Draft must be checked against the selected Studio DMC when provided.
        $studioDmcId = $request->query('dmc_id');
        if ($studioDmcId !== null && $studioDmcId !== '') {
            $studioDmcId = (int) $studioDmcId;
            $this->draftService->findDmcOrFail($studioDmcId);
            if ((int) ($tour->dmc_id ?? 0) !== $studioDmcId) {
                return back()->withErrors([
                    'tour_id' => 'Tour '.$tourId.' does not belong to DMC '.$studioDmcId.'.',
                ]);
            }
        }

        $draftName = $this->resolveDraftBasename($name, (string) $request->query('type', ''));
        $base = resource_path('quotation-templates/drafts/'.$draftName);
        $htmlPath = $base.'.html';
        $cssPath = $base.'.css';

        if (! File::isFile($htmlPath)) {
            abort(404, 'Draft HTML file not found: '.$draftName.'.html');
        }

        $html = File::get($htmlPath);
        $css = File::isFile($cssPath) ? File::get($cssPath) : '';
        $viewName = $this->viewNameForDraft($draftName, (string) $request->query('type', ''));
        $logoType = (string) ($validated['logo_type'] ?? 'dmc');
        $currency = isset($validated['currency']) ? strtoupper((string) $validated['currency']) : null;

        try {
            $response = CommonHelper::previewLocalQuotationTemplateDraft(
                $tourId,
                $html,
                $css,
                $currency,
                $viewName,
                $logoType,
                $asPdf
            );
        } catch (QuotationTemplateRenderException $e) {
            return back()->withErrors([
                'tour_id' => 'Draft render failed: '.$e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'tour_id' => 'Draft preview failed. Check the draft HTML/CSS and tour data.',
            ]);
        }

        if ($response === null) {
            return back()->withErrors(['tour_id' => 'Unable to build quotation data for this tour.']);
        }

        return $response;
    }

    private function resolveDraftBasename(string $name, string $type): string
    {
        if ($name !== '') {
            return $name;
        }

        return $type === QuotationTemplate::TYPE_DETAILED ? 'detailed' : 'acco-service';
    }

    private function viewNameForDraft(string $draftName, string $type): string
    {
        if ($draftName === 'detailed' || $type === QuotationTemplate::TYPE_DETAILED) {
            return 'single-tour-package.detailedqutation';
        }

        return 'single-tour-package.quotation';
    }

    private function persistDraft(Request $request, int $dmcId, string $type, string $successMessage): RedirectResponse
    {
        $validated = $request->validate([
            'html_content' => [
                'required',
                'string',
                'max:'.QuotationTemplateDraftService::MAX_HTML_CHARS,
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! is_string($value) || trim($value) === '') {
                        $fail('The HTML content cannot be empty.');
                    }
                },
            ],
            'css_content' => [
                'nullable',
                'string',
                'max:'.QuotationTemplateDraftService::MAX_CSS_CHARS,
            ],
            'change_notes' => [
                'nullable',
                'string',
                'max:'.QuotationTemplateDraftService::MAX_CHANGE_NOTES_CHARS,
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
            ->route('quotation-templates.show', ['dmcId' => $dmcId, 'type' => $type])
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
        if (! in_array($type, [
            QuotationTemplate::TYPE_ITINERARY,
            QuotationTemplate::TYPE_DETAILED,
        ], true)) {
            abort(404);
        }
    }

    /**
     * Documentation sourced from QuotationTemplateRenderer (Phase 2.3).
     *
     * @return array{tags: list<string>, filters: list<string>, functions: list<string>, variables: list<string>}
     */
    private function twigDocumentation(): array
    {
        $ref = new ReflectionClass(QuotationTemplateRenderer::class);

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
