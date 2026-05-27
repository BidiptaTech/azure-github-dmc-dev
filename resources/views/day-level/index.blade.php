@extends('layouts.layout')
@section('title', 'Day Level List')
@extends('layouts.datatablecss')

@section('content')
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

                <table class="datatables-basic table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Master DMC</th>
                            <th>DMC</th>
                            <th>Country</th>
                            <th>Created At</th>
                            <th>Packages</th>
                            <th>Action</th>
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
                                            <div class="mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                                <div class="small fw-semibold">
                                                    {{ $pkg['cities'] ? implode(', ', $pkg['cities']) : 'Package' }}
                                                    · {{ $pkg['total_days'] ?: $pkg['max_day'] }} day(s)
                                                </div>
                                                @if($pkg['has_stable_id'])
                                                    <a href="{{ route('day-level.edit', ['day_level' => $row->id, 'package_id' => $pkg['package_id']]) }}" class="btn btn-outline-warning btn-sm mt-1 py-0 px-2">
                                                        Edit package
                                                    </a>
                                                @else
                                                    <span class="text-muted small d-block">Legacy package</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @if(count($packageSummaries) <= 1)
                                            <a href="{{ route('day-level.edit', $row->id) }}" class="btn btn-outline-warning btn-sm">
                                                Edit
                                            </a>
                                        @endif
                                        <a href="{{ route('day-level.show', $row->id) }}" class="btn btn-outline-secondary btn-sm">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No Day Level records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script>
    $(document).ready(function () {
        $('.datatables-basic').DataTable({
            responsive: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search...'
            }
        });
    });
</script>
@endsection
