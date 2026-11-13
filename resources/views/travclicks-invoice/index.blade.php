@extends('layouts.layout')

@section('title', 'Travclicks Invoice')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">General Settings /</span> Travclicks Invoice
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
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

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Generate Monthly Travclicks Invoice</h5>
            <small class="text-muted">
                Includes Actual tours whose start date falls in the selected month (end-date filter temporarily disabled for testing).
                Prices are in SGD from Travclicks invoice rates; DMC offers apply as discounts when the tour start date falls in an offer range.
            </small>
        </div>
        <div class="card-body">
            <form action="{{ route('travclicks-invoice.generate') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label"><strong>Month</strong> <span class="text-danger">*</span></label>
                    <select name="month" class="form-select" required>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" @selected((int) $selectedMonth === $m)>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><strong>Year</strong> <span class="text-danger">*</span></label>
                    <select name="year" class="form-select" required>
                        @foreach($years as $y)
                            <option value="{{ $y }}" @selected((int) $selectedYear === (int) $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-file-list-3-line me-1"></i> Generate Invoice
                    </button>
                    @if(!empty($downloadUrl))
                        <a href="{{ $downloadUrl }}" class="btn btn-outline-secondary">
                            <i class="ri-download-2-line me-1"></i> Download PDF
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if(!empty($showPreview) && !empty($previewUrl))
        <div class="card" id="travclicks-invoice-preview">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Invoice Preview — {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}</h5>
                <a href="{{ $downloadUrl }}" class="btn btn-sm btn-primary">
                    <i class="ri-download-2-line me-1"></i> Download PDF
                </a>
            </div>
            <div class="card-body p-0">
                <iframe
                    src="{{ $previewUrl }}"
                    title="Travclicks Invoice Preview"
                    style="width:100%; min-height:920px; border:0; background:#fff;"
                ></iframe>
            </div>
        </div>
        <script>
            document.getElementById('travclicks-invoice-preview')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        </script>
    @endif
</div>
@endsection
