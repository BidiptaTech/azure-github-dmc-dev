{{-- Ledger container: hidden until a DMC is picked in the DMC Wallets card (see dmc-ledger.blade.php). --}}
<div class="card border-0 shadow-sm dmc-pane d-none" id="dmc-ledger-detail">
    <div class="card-header bg-transparent">
        <h5 class="mb-0"><i class="ri-file-list-3-line me-1"></i> Ledger</h5>
    </div>
    <div class="position-relative" style="min-height:140px;">
        <div class="dmc-loader d-none position-absolute top-0 start-0 w-100 h-100 align-items-center justify-content-center" style="z-index:5;background:rgba(255,255,255,.78);">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
        <div id="dmc-ledger-body"></div>
    </div>
</div>
