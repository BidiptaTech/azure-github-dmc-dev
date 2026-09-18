@extends('layouts.layout')
@section('title', 'Day Level List')
@extends('layouts.datatablecss')

@section('content')
<style>
    .day-level-list-table th,
    .day-level-list-table td {
        vertical-align: middle;
    }
    .day-level-list-table .action-cell-inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
        min-width: 180px;
    }
    .day-level-list-table .inclusion-cell {
        min-width: 110px;
        white-space: nowrap;
    }
    .day-level-list-table .inclusion-check-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        margin: 0;
        cursor: pointer;
        user-select: none;
    }
    .day-level-list-table .inclusion-check-wrap .form-check-input {
        width: 1.05rem;
        height: 1.05rem;
        margin: 0;
        cursor: pointer;
        flex-shrink: 0;
        border-radius: 0.2rem;
        float: none;
    }
    .day-level-list-table .inclusion-check-wrap .inclusion-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #566a7f;
        line-height: 1.2;
    }
    .day-level-list-table .package-block + .package-block {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #eceef1;
    }
</style>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center m-3">
                    <h5 class="card-title mb-0">Day Level List</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('day-level.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                            <i class="fas fa-plus"></i> Add Day Level
                        </a>
                    </div>
                </div>

                <x-alert />

                <table class="datatables-basic table table-bordered day-level-list-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Master DMC</th>
                            <th>DMC</th>
                            <th>Country</th>
                            <th>Created At</th>
                            <th>Packages</th>
                            <th style="min-width:140px">Action</th>
                            <th class="text-center" style="min-width:110px">Inclusion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dayLevels as $index => $row)
                            @php
                                $packageSummaries = $row->collectPackageSummaries();
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ optional($row->masterDmc)->company_name ?: 'Master DMC' }}
                                </td>
                                <td>
                                    {{ optional($row->dmc)->company_name ?: 'DMC' }}
                                </td>
                                <td>{{ $row->country ?: 'N/A' }}</td>
                                <td>
                                    @if($row->created_at)
                                        {{ $row->created_at->format('d M Y h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if(count($packageSummaries) === 0)
                                        <span class="text-muted small">No packages</span>
                                    @else
                                        @foreach($packageSummaries as $pkg)
                                            <div class="package-block {{ !$loop->last ? 'mb-0' : '' }}">
                                                <div class="small fw-semibold">
                                                    {{ $pkg['cities'] ? implode(', ', $pkg['cities']) : 'Package' }}
                                                    · {{ $pkg['total_days'] ?: $pkg['max_day'] }} day(s)
                                                </div>
                                                @if($pkg['has_stable_id'])
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        <a href="{{ route('day-level.edit', ['day_level' => $row->id, 'package_id' => $pkg['package_id']]) }}" class="btn btn-outline-warning btn-sm py-0 px-2">
                                                            Edit package
                                                        </a>
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-danger btn-sm py-0 px-2 day-level-delete-package-btn"
                                                            data-url="{{ route('day-level.destroy-package', ['day_level' => $row->id, 'package_id' => $pkg['package_id']]) }}"
                                                            data-label="{{ ($pkg['cities'] ? implode(', ', $pkg['cities']) : 'Package') . ' · ' . ($pkg['total_days'] ?: $pkg['max_day']) . ' day(s)' }}"
                                                        >
                                                            Delete package
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted small d-block">Legacy package</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    <div class="action-cell-inner">
                                        @if(count($packageSummaries) <= 1)
                                            <a href="{{ route('day-level.edit', $row->id) }}" class="btn btn-outline-warning btn-sm">
                                                Edit
                                            </a>
                                        @endif
                                        <a href="{{ route('day-level.show', $row->id) }}" class="btn btn-outline-secondary btn-sm">
                                            View
                                        </a>
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger btn-sm day-level-delete-row-btn"
                                            data-url="{{ route('day-level.destroy', $row->id) }}"
                                            data-label="{{ (optional($row->masterDmc)->company_name ?: 'Master DMC') . ' / ' . (optional($row->dmc)->company_name ?: 'DMC') . ' / ' . ($row->country ?: 'N/A') }}"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center inclusion-cell">
                                    <label class="inclusion-check-wrap" for="inclusion_{{ $row->id }}" title="Mark this package row as inclusion">
                                        <input
                                            type="checkbox"
                                            class="form-check-input day-level-inclusion-checkbox"
                                            id="inclusion_{{ $row->id }}"
                                            data-url="{{ route('day-level.update-inclusion', $row->id) }}"
                                            @checked((bool) $row->is_inclusion)
                                        >
                                        <span class="inclusion-label">{{ (bool) $row->is_inclusion ? 'Yes' : 'No' }}</span>
                                    </label>
                                </td>
                            </tr>
                        @empty
                            {{-- <tr>
                                <td colspan="8" class="text-center text-muted">No Day Level records found.</td>
                            </tr> --}}
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script>
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 3000,
        };
    }

    $(document).ready(function () {
        $('.datatables-basic').DataTable({
            responsive: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            columnDefs: [
                { targets: [6, 7], orderable: false, searchable: false },
                { targets: 7, className: 'text-center' }
            ],
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search...'
            }
        });

        $(document).on('change', '.day-level-inclusion-checkbox', function () {
            const checkbox = this;
            const url = checkbox.dataset.url;
            const isInclusion = checkbox.checked;
            const previous = !isInclusion;
            const label = checkbox.closest('.inclusion-check-wrap')?.querySelector('.inclusion-label');

            checkbox.disabled = true;
            if (label) label.textContent = isInclusion ? 'Yes' : 'No';

            $.ajax({
                url: url,
                method: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_inclusion: isInclusion ? 1 : 0,
                },
                success: function (response) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Inclusion updated successfully.');
                    }
                },
                error: function (xhr) {
                    checkbox.checked = previous;
                    if (label) label.textContent = previous ? 'Yes' : 'No';
                    const message = xhr.responseJSON?.message || 'Could not update inclusion.';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(message);
                    } else {
                        alert(message);
                    }
                },
                complete: function () {
                    checkbox.disabled = false;
                }
            });
        });

        function softDeleteDayLevel(url, confirmText) {
            if (!window.confirm(confirmText)) {
                return;
            }

            $.ajax({
                url: url,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function (response) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Deleted successfully.');
                    }
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 600);
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message || 'Could not delete. Please try again.';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(message);
                    } else {
                        alert(message);
                    }
                }
            });
        }

        $(document).on('click', '.day-level-delete-row-btn', function () {
            const url = this.dataset.url;
            const label = this.dataset.label || 'this Day Level';
            softDeleteDayLevel(
                url,
                'Soft-delete ' + label + '?\n\nIt will be removed from the list and from Azure JSON.'
            );
        });

        $(document).on('click', '.day-level-delete-package-btn', function () {
            const url = this.dataset.url;
            const label = this.dataset.label || 'this package';
            softDeleteDayLevel(
                url,
                'Delete package "' + label + '"?\n\nIt will be removed from this Day Level and from Azure JSON.'
            );
        });
    });
</script>
@endsection
