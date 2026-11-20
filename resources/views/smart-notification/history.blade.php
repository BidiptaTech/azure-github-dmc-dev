@extends('layouts.layout')

@section('title', 'Notification History')

@push('css')
<style>
    .ntf-history-table { font-size: 0.8125rem; margin: 0; }
    .ntf-history-table thead th {
        background: rgb(6, 132, 216);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        padding: 0.5rem 0.65rem;
        white-space: nowrap;
        border: none;
    }
    .ntf-history-table tbody td {
        padding: 0.5rem 0.65rem;
        vertical-align: top;
    }
    .ntf-receiver-badge {
        display: inline-block;
        background: #eef2ff;
        color: #4338ca;
        border-radius: 0.25rem;
        padding: 0.15rem 0.45rem;
        margin: 0 0.25rem 0.25rem 0;
        font-size: 0.75rem;
    }
    .ntf-message-cell {
        max-width: 280px;
        white-space: normal;
        word-break: break-word;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Smart Notification /</span> Notification History
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Notification History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ntf-history-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Receivers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notificationHistory as $history)
                            @php
                                $typeLabels = [
                                    'dmc' => 'DMC',
                                    'agents' => 'Agents',
                                    'operations' => 'Operation',
                                    'guests' => 'Guests',
                                    'drivers' => 'Drivers',
                                    'guides' => 'Guides',
                                ];
                                $receivers = is_array($history->receiver) ? $history->receiver : [];
                            @endphp
                            <tr>
                                <td>{{ $history->id }}</td>
                                <td>{{ $history->created_at ? $history->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                <td>{{ $typeLabels[$history->sender_type] ?? ucfirst((string) $history->sender_type) }}</td>
                                <td>{{ $history->title }}</td>
                                <td class="ntf-message-cell">{{ $history->message }}</td>
                                <td>
                                    @forelse($receivers as $receiver)
                                        <span class="ntf-receiver-badge">
                                            {{ $receiver['name'] ?? 'Unknown' }} ({{ $receiver['email'] ?? 'N/A' }})
                                        </span>
                                    @empty
                                        <span class="text-muted">N/A</span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No notification history yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
