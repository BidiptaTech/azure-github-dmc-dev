@extends('layouts.layout')

@section('title', 'Default Values')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @php
        $activeCount = $defaultValues->where('status', 1)->count();
        $inactiveCount = $defaultValues->where('status', 0)->count();
        $cityCount = $defaultValues->pluck('country')->filter()->zip($defaultValues->pluck('city')->filter())->map(function ($pair) {
            return implode('::', $pair->all());
        })->unique()->count();
        $typeCount = $defaultValues->pluck('name')->filter()->unique()->count();
        $countries = $defaultValues->pluck('country')->filter()->unique()->sort()->values();
        $cities = $defaultValues->pluck('city')->filter()->unique()->sort()->values();
        $types = $defaultValues->pluck('name')->filter()->unique()->values();
        $typeLabels = [
            'hotel' => 'Hotel',
            'restaurant' => 'Restaurant',
            'attraction' => 'Attraction',
            'car_private' => 'Car (Private)',
            'car_shared' => 'Car (Shared)',
            'port' => 'Port',
            'guide' => 'Guide',
        ];
    @endphp
    <style>
        .default-list-stat {
            border: 1px solid #e7eaf3;
            border-radius: 14px;
            padding: 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
            height: 100%;
        }
        .default-list-stat-label {
            color: #98a2b3;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .default-list-stat-value {
            font-size: 1.55rem;
            font-weight: 700;
            color: #344054;
            margin-top: 0.2rem;
        }
        .default-filter-card {
            border: 1px solid #e7eaf3;
            border-radius: 14px;
            background: #fbfcff;
        }
    </style>
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Default Values /</span> List
    </h4>

    <!-- Display flash messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var alert = document.getElementById('success-alert');
                if (alert) {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            }, 3000);
        });
    </script>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var alert = document.getElementById('error-alert');
                if (alert) {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            }, 3000);
        });
    </script>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Default Values Configuration</h5>
            @if(!empty($availableTypes))
            <a href="{{ route('default-values.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>Add Default Value
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="ri-information-line me-2"></i>
                Configure defaults per <strong>country + city</strong> (e.g. Hotel for Singapore, Hotel for Batam / Indonesia).
                Each service type can be set once per city. The Enquiry Form Pro applies these when that city is selected.
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="default-list-stat">
                        <div class="default-list-stat-label">Total Mappings</div>
                        <div class="default-list-stat-value">{{ $defaultValues->count() }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="default-list-stat">
                        <div class="default-list-stat-label">Active</div>
                        <div class="default-list-stat-value">{{ $activeCount }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="default-list-stat">
                        <div class="default-list-stat-label">Cities Covered</div>
                        <div class="default-list-stat-value">{{ $cityCount }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="default-list-stat">
                        <div class="default-list-stat-label">Service Types Used</div>
                        <div class="default-list-stat-value">{{ $typeCount }}</div>
                    </div>
                </div>
            </div>

            <div class="card default-filter-card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="defaultFilterSearch" class="form-label">Search</label>
                            <input type="text" id="defaultFilterSearch" class="form-control" placeholder="Search service or city">
                        </div>
                        <div class="col-md-3">
                            <label for="defaultFilterCountry" class="form-label">Country</label>
                            <select id="defaultFilterCountry" class="form-select">
                                <option value="">All Countries</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="defaultFilterCity" class="form-label">City</label>
                            <select id="defaultFilterCity" class="form-select">
                                <option value="">All Cities</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="defaultFilterType" class="form-label">Type</label>
                            <select id="defaultFilterType" class="form-select">
                                <option value="">All Types</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}">{{ $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="defaultFilterStatus" class="form-label">Status</label>
                            <select id="defaultFilterStatus" class="form-select">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-bordered" id="defaultValuesTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Country</th>
                            <th>City</th>
                            <th>Service Type</th>
                            <th>Service Name</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($defaultValues as $key => $defaultValue)
                        @php
                            $serviceName = 'N/A';
                            if ($defaultValue->name == 'hotel' && $defaultValue->hotel) {
                                $serviceName = $defaultValue->hotel->name ?? 'N/A';
                            } elseif ($defaultValue->name == 'restaurant' && $defaultValue->restaurant) {
                                $serviceName = $defaultValue->restaurant->name ?? 'N/A';
                            } elseif ($defaultValue->name == 'attraction' && $defaultValue->attraction) {
                                $serviceName = $defaultValue->attraction->name ?? 'N/A';
                            } elseif (($defaultValue->name == 'car_private' || $defaultValue->name == 'car_shared') && $defaultValue->vehicle) {
                                $serviceName = $defaultValue->vehicle->vehicle_name ?? 'N/A';
                            } elseif ($defaultValue->name == 'port' && $defaultValue->port) {
                                $serviceName = $defaultValue->port->port_name ?? 'N/A';
                            } elseif ($defaultValue->name == 'guide' && $defaultValue->guide) {
                                $serviceName = $defaultValue->guide->name ?? 'N/A';
                            }
                        @endphp
                        <tr
                            data-country="{{ strtolower($defaultValue->country ?: '') }}"
                            data-city="{{ strtolower($defaultValue->city ?: '') }}"
                            data-type="{{ strtolower($defaultValue->name ?: '') }}"
                            data-status="{{ (string) $defaultValue->status }}"
                            data-search="{{ strtolower(($defaultValue->country ?: '') . ' ' . ($defaultValue->city ?: '') . ' ' . ($defaultValue->name ?: '') . ' ' . $serviceName) }}"
                        >
                            <td>{{ ++$key }}</td>
                            <td>{{ $defaultValue->country ?: '—' }}</td>
                            <td>{{ $defaultValue->city ?: '—' }}</td>
                            <td>
                                @if($defaultValue->name == 'hotel')
                                    <span class="badge bg-success"><i class="ri-hotel-line me-1"></i>Hotel</span>
                                @elseif($defaultValue->name == 'restaurant')
                                    <span class="badge bg-warning"><i class="ri-restaurant-2-line me-1"></i>Restaurant</span>
                                @elseif($defaultValue->name == 'attraction')
                                    <span class="badge bg-info"><i class="ri-landscape-line me-1"></i>Attraction</span>
                                @elseif($defaultValue->name == 'car_private')
                                    <span class="badge bg-primary"><i class="ri-car-line me-1"></i>Car (Private)</span>
                                @elseif($defaultValue->name == 'car_shared')
                                    <span class="badge bg-secondary"><i class="ri-taxi-line me-1"></i>Car (Shared)</span>
                                @elseif($defaultValue->name == 'port')
                                    <span class="badge bg-dark"><i class="ri-ship-line me-1"></i>Port</span>
                                @elseif($defaultValue->name == 'guide')
                                    <span class="badge" style="background-color: #6f42c1; color: white;"><i class="ri-user-star-line me-1"></i>Guide</span>
                                @endif
                            </td>
                            <td>
                                <div style="max-width: 300px; word-wrap: break-word; white-space: normal;">
                                    {{ $serviceName }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $defaultValue->status == 1 ? 'success' : 'danger' }}">
                                    {{ $defaultValue->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $defaultValue->updated_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('default-values.edit', Crypt::encrypt($defaultValue->id)) }}" 
                                       class="btn btn-primary btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                       style="width: 28px; height: 28px; padding: 0;" title="Edit">
                                        <i class="ri-pencil-line" style="font-size: 16px;"></i>
                                    </a>
                                    
                                    <form action="{{ route('default-values.destroy', Crypt::encrypt($defaultValue->id)) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-danger btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                                style="width: 28px; height: 28px; padding: 0;" title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this default value?')">
                                            <i class="ri-delete-bin-line" style="font-size: 16px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="py-4">
                                    <i class="ri-information-line" style="font-size: 48px; color: #ccc;"></i>
                                    <p class="mt-2 mb-0">No default values configured yet.</p>
                                    <a href="{{ route('default-values.create') }}" class="btn btn-primary btn-sm mt-2">
                                        <i class="ri-add-line me-1"></i>Add Your First Default Value
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filters = {
        search: document.getElementById('defaultFilterSearch'),
        country: document.getElementById('defaultFilterCountry'),
        city: document.getElementById('defaultFilterCity'),
        type: document.getElementById('defaultFilterType'),
        status: document.getElementById('defaultFilterStatus')
    };
    const rows = Array.from(document.querySelectorAll('#defaultValuesTable tbody tr[data-country]'));

    function applyFilters() {
        const search = (filters.search?.value || '').toLowerCase().trim();
        const country = (filters.country?.value || '').toLowerCase();
        const city = (filters.city?.value || '').toLowerCase();
        const type = (filters.type?.value || '').toLowerCase();
        const status = filters.status?.value || '';

        rows.forEach(function (row) {
            const visible = (!search || row.dataset.search.includes(search))
                && (!country || row.dataset.country === country)
                && (!city || row.dataset.city === city)
                && (!type || row.dataset.type === type)
                && (!status || row.dataset.status === status);

            row.style.display = visible ? '' : 'none';
        });
    }

    Object.values(filters).forEach(function (input) {
        if (!input) return;
        input.addEventListener('input', applyFilters);
        input.addEventListener('change', applyFilters);
    });
});
</script>
@endpush

