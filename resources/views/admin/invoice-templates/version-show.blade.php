@extends('layouts.layout')
@section('title', 'Invoice Template Version v'.$versionModel->version)

@section('css')
<style>
    .qts-wrap { max-width: 1100px; margin: 0 auto; }
    .qts-card {
        border: 1px solid #d0d7e2;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
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
    .qts-header h1 { margin: 0; font-size: 1.25rem; font-weight: 800; color: #1e293b; }
    .qts-header p { margin: 0.2rem 0 0; color: #64748b; font-size: 13px; }
    .qts-body { padding: 1.25rem 1.5rem 1.5rem; }
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
    .qts-meta-item .label { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; }
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
        min-height: 260px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12.5px;
        line-height: 1.45;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.75rem;
        background: #f1f5f9;
        color: #0f172a;
        resize: vertical;
    }
    .qts-code.css { min-height: 140px; }
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
    .qts-note {
        font-size: 13px;
        color: #64748b;
        margin-top: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="qts-wrap">
        <div class="qts-card">
            <div class="qts-header">
                <div>
                    <h1>Version v{{ $versionModel->version }} — {{ $types[$type] ?? $type }}</h1>
                    <p>{{ $dmc->name }} (DMC {{ $dmc->userId }}) · Read-only inspection · Content is escaped and not executed</p>
                </div>
                <a href="{{ route('invoice-templates.show', ['dmcId' => $dmc->userId, 'type' => $type]) }}"
                   class="btn btn-outline-secondary btn-sm">Back to Studio</a>
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

                <div class="qts-meta-grid">
                    <div class="qts-meta-item">
                        <span class="label">Status</span>
                        <div class="value"><span class="qts-status {{ $versionModel->status }}">{{ $versionModel->status }}</span></div>
                    </div>
                    <div class="qts-meta-item">
                        <span class="label">Live for PDFs</span>
                        <div class="value">
                            @if($isActiveVersion)
                                <span class="qts-status published">Active</span>
                            @else
                                <span class="qts-status archived">Not active</span>
                            @endif
                        </div>
                    </div>
                    <div class="qts-meta-item">
                        <span class="label">Created</span>
                        <div class="value">{{ optional($versionModel->created_at)->format('Y-m-d H:i') }}</div>
                    </div>
                    <div class="qts-meta-item">
                        <span class="label">Created by</span>
                        <div class="value">{{ optional($versionModel->createdBy)->name ?? '—' }}</div>
                    </div>
                    <div class="qts-meta-item">
                        <span class="label">Published at</span>
                        <div class="value">{{ optional($versionModel->published_at)->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                </div>

                <div class="qts-meta-item mb-3">
                    <span class="label">Change notes</span>
                    <div class="value">{{ $versionModel->change_notes ?: '—' }}</div>
                </div>

                @if($versionModel->status === 'published')
                    <div style="display:flex;gap:0.6rem;flex-wrap:wrap;margin-bottom:1rem;">
                        @if($isActiveVersion)
                            <form method="post"
                                  action="{{ route('invoice-templates.deactivate', ['dmcId' => $dmc->userId, 'type' => $type]) }}"
                                  onsubmit="return confirm('Deactivate custom template? This DMC will use the system default invoice.');">
                                @csrf
                                <input type="hidden" name="return" value="version">
                                <input type="hidden" name="version" value="{{ $versionModel->version }}">
                                <button type="submit" class="btn btn-outline-warning btn-sm">Deactivate custom template</button>
                            </form>
                        @else
                            <form method="post"
                                  action="{{ route('invoice-templates.activate', ['dmcId' => $dmc->userId, 'type' => $type]) }}"
                                  onsubmit="return confirm('Activate v{{ $versionModel->version }} for this DMC?');">
                                @csrf
                                <input type="hidden" name="return" value="version">
                                <input type="hidden" name="version" value="{{ $versionModel->version }}">
                                <button type="submit" class="btn btn-outline-success btn-sm">Activate this version</button>
                            </form>
                        @endif
                    </div>
                @endif

                <label class="qts-editor-label">HTML content (read-only, not rendered)</label>
                <textarea class="qts-code" readonly spellcheck="false">{{ $versionModel->html_content }}</textarea>

                <label class="qts-editor-label">CSS content (read-only, not rendered)</label>
                <textarea class="qts-code css" readonly spellcheck="false">{{ $versionModel->css_content }}</textarea>

                <p class="qts-note">
                    Published and archived versions cannot be edited here.
                    Deactivate clears <code>active_version_id</code> (system default PDFs) without deleting the published version.
                    To change content, return to the Studio editor and save a new draft, then publish.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
