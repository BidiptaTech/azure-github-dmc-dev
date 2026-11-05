@extends('layouts.layout')
@section('title', 'Invoice Template Studio')

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
    }
    .qts-header h1 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
    }
    .qts-header p {
        margin: 0.25rem 0 0;
        color: #64748b;
        font-size: 13px;
    }
    .qts-body { padding: 1.25rem 1.5rem 1.5rem; }
    .qts-search { display: flex; gap: 0.75rem; margin-bottom: 1rem; }
    .qts-search input {
        flex: 1;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.55rem 0.85rem;
    }
    .qts-table { width: 100%; border-collapse: collapse; }
    .qts-table th, .qts-table td {
        text-align: left;
        padding: 0.7rem 0.6rem;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13.5px;
        vertical-align: middle;
    }
    .qts-table th { color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.03em; }
    .qts-badge {
        display: inline-block;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: #e2e8f0;
        color: #334155;
    }
    .qts-badge.yes { background: #dcfce7; color: #166534; }
    .qts-badge.no { background: #f1f5f9; color: #64748b; }
    .qts-badge.inactive { background: #fef3c7; color: #92400e; }
    .qts-toggle-form { display: inline; margin-left: 0.35rem; }
    .qts-toggle-form button {
        border: 1px solid #cbd5e1;
        background: #fff;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        padding: 0.15rem 0.45rem;
        color: #334155;
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="qts-wrap">
        <div class="qts-card">
            <div class="qts-header">
                <h1>Invoice Template Studio</h1>
                <p>Select a DMC to manage custom proforma, final, and alternate invoice templates. Opening this page does not create templates.</p>
            </div>
            <div class="qts-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="get" action="{{ route('invoice-templates.index') }}" class="qts-search">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search DMC by name, email, or ID" autocomplete="off">
                    <button type="submit" class="btn btn-primary">Search</button>
                    @if($search !== '')
                        <a href="{{ route('invoice-templates.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </form>

                <table class="qts-table">
                    <thead>
                        <tr>
                            <th>DMC ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Proforma</th>
                            <th>Final</th>
                            <th>Alternate</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dmcs as $dmc)
                            @php
                                $types = $templateSummary->get($dmc->userId, collect());
                                $proforma = $types->firstWhere('invoice_type', 'proforma');
                                $final = $types->firstWhere('invoice_type', 'final');
                                $alternate = $types->firstWhere('invoice_type', 'alternate');
                                $proformaActive = $proforma && !empty($proforma->active_version_id);
                                $finalActive = $final && !empty($final->active_version_id);
                                $alternateActive = $alternate && !empty($alternate->active_version_id);
                            @endphp
                            <tr>
                                <td>{{ $dmc->userId }}</td>
                                <td>{{ $dmc->name }}</td>
                                <td>{{ $dmc->email }}</td>
                                <td>
                                    @if(!$proforma)
                                        <span class="qts-badge no">System default</span>
                                    @elseif($proformaActive)
                                        <span class="qts-badge yes">Custom · Active</span>
                                        <form method="post" class="qts-toggle-form"
                                              action="{{ route('invoice-templates.deactivate', ['dmcId' => $dmc->userId, 'type' => 'proforma']) }}"
                                              onsubmit="return confirm('Deactivate Proforma custom template for {{ $dmc->name }}?');">
                                            @csrf
                                            <input type="hidden" name="return" value="index">
                                            <button type="submit">Deactivate</button>
                                        </form>
                                    @else
                                        <span class="qts-badge inactive">Custom · Inactive</span>
                                        <form method="post" class="qts-toggle-form"
                                              action="{{ route('invoice-templates.activate', ['dmcId' => $dmc->userId, 'type' => 'proforma']) }}"
                                              onsubmit="return confirm('Activate Proforma custom template for {{ $dmc->name }}?');">
                                            @csrf
                                            <input type="hidden" name="return" value="index">
                                            <button type="submit">Activate</button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    @if(!$final)
                                        <span class="qts-badge no">System default</span>
                                    @elseif($finalActive)
                                        <span class="qts-badge yes">Custom · Active</span>
                                        <form method="post" class="qts-toggle-form"
                                              action="{{ route('invoice-templates.deactivate', ['dmcId' => $dmc->userId, 'type' => 'final']) }}"
                                              onsubmit="return confirm('Deactivate Final custom template for {{ $dmc->name }}?');">
                                            @csrf
                                            <input type="hidden" name="return" value="index">
                                            <button type="submit">Deactivate</button>
                                        </form>
                                    @else
                                        <span class="qts-badge inactive">Custom · Inactive</span>
                                        <form method="post" class="qts-toggle-form"
                                              action="{{ route('invoice-templates.activate', ['dmcId' => $dmc->userId, 'type' => 'final']) }}"
                                              onsubmit="return confirm('Activate Final custom template for {{ $dmc->name }}?');">
                                            @csrf
                                            <input type="hidden" name="return" value="index">
                                            <button type="submit">Activate</button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    @if(!$alternate)
                                        <span class="qts-badge no">System default</span>
                                    @elseif($alternateActive)
                                        <span class="qts-badge yes">Custom · Active</span>
                                        <form method="post" class="qts-toggle-form"
                                              action="{{ route('invoice-templates.deactivate', ['dmcId' => $dmc->userId, 'type' => 'alternate']) }}"
                                              onsubmit="return confirm('Deactivate Alternate custom template for {{ $dmc->name }}?');">
                                            @csrf
                                            <input type="hidden" name="return" value="index">
                                            <button type="submit">Deactivate</button>
                                        </form>
                                    @else
                                        <span class="qts-badge inactive">Custom · Inactive</span>
                                        <form method="post" class="qts-toggle-form"
                                              action="{{ route('invoice-templates.activate', ['dmcId' => $dmc->userId, 'type' => 'alternate']) }}"
                                              onsubmit="return confirm('Activate Alternate custom template for {{ $dmc->name }}?');">
                                            @csrf
                                            <input type="hidden" name="return" value="index">
                                            <button type="submit">Activate</button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-primary"
                                       href="{{ route('invoice-templates.show', ['dmcId' => $dmc->userId, 'type' => 'proforma']) }}">
                                        Open Studio
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">No DMC accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
