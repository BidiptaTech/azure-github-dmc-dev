<div class="row g-2">
    @forelse($services as $service)
        <div class="col-md-3">
            <div class="card p-2">
                <h6 class="mb-1">{{ $service->name }}</h6>
                <p class="mb-0 small text-muted">{{ optional($service->date)->format('Y-m-d') }}</p>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-light border mb-0">
                No services found for the selected dates.
            </div>
        </div>
    @endforelse
</div>

