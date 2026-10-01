{{-- === STP LITE: 05-phase1-stub ===
     Depends: guest-caps.js (data-guest-cap / data-guest-child-ui)
     Owns: Phase 1 demo of global guest caps for later service sections
     === --}}
<div class="stp-lite-card" id="stpLitePhase1Stub">
    <div class="stp-lite-card-header">
        <div>
            <h2>Phase 1 — Guest Caps Preview</h2>
            <small>Service sections (Hotel → …) arrive next; this stub proves global caps work</small>
        </div>
    </div>
    <div class="stp-lite-card-body">
        <div class="stp-lite-stub mb-3" id="phase1GuestCapsStub"></div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="stp-lite-label">Section Adults (capped)</label>
                <input type="number" class="form-control stp-lite-int" min="0" value="1" data-guest-cap="adults">
            </div>
            <div class="col-md-4" data-guest-child-ui>
                <label class="stp-lite-label">Section Children (capped / hidden if 0)</label>
                <input type="number" class="form-control stp-lite-int" min="0" value="0" data-guest-cap="children">
            </div>
            <div class="col-md-4">
                <label class="stp-lite-label">Section Infants (capped)</label>
                <input type="number" class="form-control stp-lite-int" min="0" value="0" data-guest-cap="infants">
            </div>
        </div>

        <div class="mt-3">
            <label class="stp-lite-label">Text sanitize test (special chars stripped)</label>
            <input type="text" class="form-control" placeholder="Try typing &lt;script&gt; or $pecial*" id="sanitizeDemoInput">
        </div>
    </div>
</div>
{{-- === END 05-phase1-stub === --}}
