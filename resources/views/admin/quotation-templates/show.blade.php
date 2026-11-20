@extends('layouts.layout')
@section('title', 'Quotation Template Studio — '.$dmc->name)

@section('css')
<style>
    .qts-wrap { max-width: 1280px; margin: 0 auto; }
    .qts-card {
        border: 1px solid #d0d7e2;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    .qts-header {
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
        border-bottom: 1px solid #d0d7e2;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: flex-start;
    }
    .qts-header h1 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        color: #1e293b;
    }
    .qts-header p { margin: 0.2rem 0 0; color: #64748b; font-size: 13px; }
    .qts-body { padding: 1.25rem 1.5rem 1.5rem; }
    .qts-tabs { display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap; }
    .qts-tabs a {
        padding: 0.45rem 0.9rem;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        color: #334155;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        background: #f8fafc;
    }
    .qts-tabs a.active {
        background: #1e293b;
        border-color: #1e293b;
        color: #fff;
    }
    .qts-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .qts-meta-item {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.7rem 0.85rem;
        background: #f8fafc;
    }
    .qts-meta-item .label { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; }
    .qts-meta-item .value { font-weight: 700; color: #1e293b; font-size: 13.5px; margin-top: 0.15rem; }
    .qts-editor-label {
        display: block;
        font-weight: 700;
        color: #334155;
        margin: 0.85rem 0 0.35rem;
        font-size: 13px;
    }
    .qts-code {
        width: 100%;
        min-height: 280px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 12.5px;
        line-height: 1.45;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.75rem;
        background: #0f172a;
        color: #e2e8f0;
        tab-size: 2;
        resize: vertical;
    }
    .qts-code.css { min-height: 160px; }
    .qts-notes {
        width: 100%;
        min-height: 70px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.6rem 0.75rem;
        font-size: 13px;
    }
    .qts-actions { display: flex; gap: 0.6rem; margin-top: 1rem; flex-wrap: wrap; }
    .qts-help details {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        background: #f8fafc;
    }
    .qts-help summary { cursor: pointer; font-weight: 700; color: #1e293b; }
    .qts-help ul { margin: 0.5rem 0 0; padding-left: 1.1rem; font-size: 13px; color: #334155; }
    .qts-help code {
        font-size: 12px;
        background: #e2e8f0;
        padding: 0.05rem 0.3rem;
        border-radius: 4px;
    }
    .qts-table { width: 100%; border-collapse: collapse; }
    .qts-table th, .qts-table td {
        text-align: left;
        padding: 0.65rem 0.5rem;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13px;
    }
    .qts-status {
        display: inline-block;
        padding: 0.12rem 0.45rem;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .qts-status.draft { background: #fef3c7; color: #92400e; }
    .qts-status.published { background: #dcfce7; color: #166534; }
    .qts-status.archived { background: #e2e8f0; color: #475569; }
    @media (max-width: 768px) {
        .qts-code { min-height: 220px; }
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="qts-wrap">
        <div class="qts-card">
            <div class="qts-header">
                <div>
                    <h1>Template Studio — {{ $dmc->name }}</h1>
                    <p>DMC ID {{ $dmc->userId }} · Templates are DMC-owned · Branding modes share this template · Twig (sandboxed), not Blade/PHP</p>
                </div>
                <a href="{{ route('quotation-templates.index') }}" class="btn btn-outline-secondary btn-sm">Back to DMC list</a>
            </div>
            <div class="qts-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="qts-tabs">
                    @foreach($types as $typeKey => $typeLabel)
                        <a href="{{ route('quotation-templates.show', ['dmcId' => $dmc->userId, 'type' => $typeKey]) }}"
                           class="{{ $type === $typeKey ? 'active' : '' }}">
                            {{ $typeLabel }}
                        </a>
                    @endforeach
                </div>

                <div class="qts-meta-grid">
                    <div class="qts-meta-item">
                        <span class="label">Custom template</span>
                        <div class="value">{{ $template ? 'Yes' : 'No — using system default' }}</div>
                    </div>
                    <div class="qts-meta-item">
                        <span class="label">Live for PDFs</span>
                        <div class="value">
                            @if($isCustomActive)
                                <span class="qts-status published">Active · v{{ $activePublished->version }}</span>
                            @elseif($latestPublished)
                                <span class="qts-status archived">Inactive · system default</span>
                            @else
                                System default
                            @endif
                        </div>
                    </div>
                    <div class="qts-meta-item">
                        <span class="label">Latest draft</span>
                        <div class="value">
                            @if($draft)
                                v{{ $draft->version }}
                            @else
                                None
                            @endif
                        </div>
                    </div>
                    <div class="qts-meta-item">
                        <span class="label">Last updated</span>
                        <div class="value">
                            @if($draft)
                                {{ optional($draft->updated_at)->format('Y-m-d H:i') }}
                            @elseif($template)
                                {{ optional($template->updated_at)->format('Y-m-d H:i') }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>

                @if($isCustomActive || $latestPublished)
                    <div class="qts-actions mb-3" style="margin-top:0;">
                        @if($isCustomActive)
                            <form method="post"
                                  action="{{ route('quotation-templates.deactivate', ['dmcId' => $dmc->userId, 'type' => $type]) }}"
                                  onsubmit="return confirm('Deactivate custom template? Enquiry PDFs for this DMC will use the system default quotation.');">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-sm">Deactivate custom template</button>
                            </form>
                        @elseif($latestPublished)
                            <form method="post"
                                  action="{{ route('quotation-templates.activate', ['dmcId' => $dmc->userId, 'type' => $type]) }}"
                                  onsubmit="return confirm('Activate published v{{ $latestPublished->version }}? Enquiry PDFs for this DMC will use the custom template.');">
                                @csrf
                                <input type="hidden" name="version" value="{{ $latestPublished->version }}">
                                <button type="submit" class="btn btn-outline-success btn-sm">Activate custom template (v{{ $latestPublished->version }})</button>
                            </form>
                        @endif
                    </div>
                @endif

                @if(!$template)
                    <p class="text-muted mb-3">No custom template exists for this quotation type. Create a draft below. Saving is the only action that writes to the database.</p>
                @endif

                @php
                    $localDraftName = $type === 'detailed' ? 'detailed' : 'acco-service';
                @endphp
                <div class="qts-card" style="border:1px solid #e2e8f0;border-radius:8px;padding:1rem;margin-bottom:1.25rem;background:#f8fafc;">
                    <h2 style="font-size:1rem;font-weight:800;margin:0 0 0.35rem;color:#1e293b;">Check local draft before saving</h2>
                    <p style="font-size:12.5px;color:#64748b;margin:0 0 0.75rem;">
                        Edit <code>resources/quotation-templates/drafts/{{ $localDraftName }}.html</code>
                        and <code>{{ $localDraftName }}.css</code>, then preview with a real tour.
                        This does <strong>not</strong> write to the database. After it looks correct, copy the file contents into the editors below and Save Draft.
                    </p>
                    <form method="get" action="{{ route('quotation-templates.drafts.preview', ['name' => $localDraftName]) }}" target="_blank" class="row g-2 align-items-end">
                        <input type="hidden" name="dmc_id" value="{{ $dmc->userId }}">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px;font-weight:700;">Tour ID</label>
                            <input type="number" name="tour_id" class="form-control" min="1" required placeholder="e.g. 12345" value="{{ old('tour_id', request('tour_id')) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size:12px;font-weight:700;">Currency (optional)</label>
                            <input type="text" name="currency" class="form-control" maxlength="10" placeholder="USD" value="{{ old('currency') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size:12px;font-weight:700;">Branding</label>
                            <select name="logo_type" class="form-select">
                                <option value="dmc">DMC</option>
                                <option value="agency">Agency</option>
                            </select>
                        </div>
                        <div class="col-md-5 d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-outline-primary btn-sm">Open HTML preview</button>
                            <button type="submit" class="btn btn-outline-secondary btn-sm"
                                    formaction="{{ route('quotation-templates.drafts.pdf', ['name' => $localDraftName]) }}">
                                Open PDF preview
                            </button>
                        </div>
                    </form>
                </div>

                <form method="post"
                      action="{{ $template
                          ? route('quotation-templates.draft.update', ['dmcId' => $dmc->userId, 'type' => $type])
                          : route('quotation-templates.store', ['dmcId' => $dmc->userId, 'type' => $type]) }}">
                    @csrf
                    @if($template)
                        @method('PUT')
                    @endif

                    <label class="qts-editor-label" for="html_content">HTML template (Twig)</label>
                    <textarea id="html_content" name="html_content" class="qts-code" spellcheck="false"
                              required>{{ old('html_content', $editableHtml) }}</textarea>

                    <label class="qts-editor-label" for="css_content">CSS stylesheet</label>
                    <textarea id="css_content" name="css_content" class="qts-code css" spellcheck="false">{{ old('css_content', $editableCss) }}</textarea>

                    <label class="qts-editor-label" for="change_notes">Change notes (optional)</label>
                    <textarea id="change_notes" name="change_notes" class="qts-notes" maxlength="2000">{{ old('change_notes', $editableNotes) }}</textarea>

                    <div class="qts-actions">
                        <button type="submit" class="btn btn-primary">
                            {{ $template ? 'Save Draft' : 'Create Draft Template' }}
                        </button>
                    </div>
                    <p class="text-muted mt-2 mb-0" style="font-size:12.5px;">
                        Saving a draft never publishes and never changes the active version.
                        @if($template && !$draft && $activePublished)
                            Editing from a published template will create a new draft version on save.
                        @elseif($draft)
                            An open draft (v{{ $draft->version }}) will be updated in place.
                        @endif
                    </p>
                </form>

                @if($draft)
                    <form method="post"
                          action="{{ route('quotation-templates.publish', ['dmcId' => $dmc->userId, 'type' => $type]) }}"
                          class="mt-3"
                          onsubmit="return confirm('Publish draft v{{ $draft->version }}? Enquiry quotation PDFs for this DMC will use this template immediately.');">
                        @csrf
                        <div class="qts-actions">
                            <button type="submit" class="btn btn-success">
                                Publish draft v{{ $draft->version }}
                            </button>
                        </div>
                        <p class="text-muted mt-2 mb-0" style="font-size:12.5px;">
                            Until you publish, production PDFs keep using
                            @if($activePublished)
                                published v{{ $activePublished->version }}
                            @else
                                the system (Blade) quotation format
                            @endif.
                        </p>
                    </form>
                @endif
            </div>
        </div>

        <div class="qts-card">
            <div class="qts-header">
                <div>
                    <h1>Version history</h1>
                    <p>Published and archived versions cannot be edited. Publish a draft to make it active for PDF generation.</p>
                </div>
            </div>
            <div class="qts-body">
                <table class="qts-table">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Created by</th>
                            <th>Notes</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versions as $v)
                            @php
                                $rowIsActive = $template && (int) ($template->active_version_id ?? 0) === (int) $v->id;
                            @endphp
                            <tr>
                                <td>
                                    v{{ $v->version }}
                                    @if($rowIsActive)
                                        <span class="qts-status published" style="margin-left:0.35rem;">live</span>
                                    @endif
                                </td>
                                <td><span class="qts-status {{ $v->status }}">{{ $v->status }}</span></td>
                                <td>{{ optional($v->created_at)->format('Y-m-d H:i') }}</td>
                                <td>{{ optional($v->createdBy)->name ?? '—' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit((string) ($v->change_notes ?? ''), 80) }}</td>
                                <td>
                                    <a href="{{ route('quotation-templates.versions.show', [
                                        'dmcId' => $dmc->userId,
                                        'type' => $type,
                                        'version' => $v->version,
                                    ]) }}">Inspect</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No versions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="qts-card qts-help">
            <div class="qts-header">
                <div>
                    <h1>Template context reference</h1>
                    <p>Source of truth: sandboxed Twig renderer (Phase 2.3). Do not use Blade, PHP, or unlisted Twig features. Local drafts to test before saving: <code>resources/quotation-templates/drafts/</code> — run <code>php artisan quotation-template:check-draft acco-service</code>.</p>
                </div>
            </div>
            <div class="qts-body">
                <details open>
                    <summary>Supported variables</summary>
                    <ul>
                        @foreach($twigDocs['variables'] as $var)
                            <li><code>{{ $var }}</code></li>
                        @endforeach
                    </ul>
                    <p style="font-size:13px;color:#475569;margin-top:0.75rem;">
                        @verbatim
                        Example access: <code>{{ branding.company_name }}</code>,
                        <code>{% for day in travel.days %}…{% endfor %}</code>,
                        <code>{{ pricing.total|number_format(2) }}</code>.
                        @endverbatim
                        Nested keys depend on the normalized quotation data contract from Phase 1.
                    </p>
                </details>
                <details class="mt-3">
                    <summary>Allowed Twig tags</summary>
                    <ul>
                        @foreach($twigDocs['tags'] as $tag)
                            <li><code>{{ $tag }}</code></li>
                        @endforeach
                    </ul>
                </details>
                <details class="mt-3">
                    <summary>Allowed Twig filters</summary>
                    <ul>
                        @foreach($twigDocs['filters'] as $filter)
                            <li><code>{{ $filter }}</code></li>
                        @endforeach
                    </ul>
                </details>
                <details class="mt-3">
                    <summary>Allowed Twig functions</summary>
                    <ul>
                        @foreach($twigDocs['functions'] as $fn)
                            <li><code>{{ $fn }}</code></li>
                        @endforeach
                    </ul>
                </details>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    function enableTabIndent(el) {
        if (!el) return;
        el.addEventListener('keydown', function (e) {
            if (e.key !== 'Tab') return;
            e.preventDefault();
            var start = el.selectionStart;
            var end = el.selectionEnd;
            var value = el.value;
            el.value = value.substring(0, start) + '  ' + value.substring(end);
            el.selectionStart = el.selectionEnd = start + 2;
        });
    }
    enableTabIndent(document.getElementById('html_content'));
    enableTabIndent(document.getElementById('css_content'));
})();
</script>
@endsection
