{{-- === STP LITE: Add Agency Contact Modal (same as classic create) === --}}
<div class="modal fade" id="addAgencyContactModal" tabindex="-1" aria-labelledby="addAgencyContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAgencyContactModalLabel">
                    <i class="ri-user-add-line me-1"></i>Add Agency Contact
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAgencyContactForm" onsubmit="return false;">
                <div class="modal-body">
                    <div id="quickAgencyContactErrors" class="alert alert-danger py-2 px-3 d-none" style="font-size: 0.85rem;"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-1">Agency Company</label>
                        <div class="form-control bg-light" id="quickAgencyContactAgencyName" style="height: 38px; line-height: 24px;"></div>
                        <input type="hidden" name="agency_id" id="quickAgencyContactAgencyId" value="">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold mb-1">Salutation <span class="text-danger">*</span></label>
                            <select class="form-select" name="salutation" required>
                                <option value="">Select</option>
                                <option value="Mr">Mr.</option>
                                <option value="Mrs">Mrs.</option>
                                <option value="Miss">Miss</option>
                                <option value="Dear">Dear</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold mb-1">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="Contact name" required>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" placeholder="Email address" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone" placeholder="Phone number" required>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label fw-semibold mb-1">Designation <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="designation" placeholder="e.g. Sales Manager" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveQuickAgencyContactBtn" onclick="window.saveQuickAgencyContact && window.saveQuickAgencyContact()">
                        <i class="ri-save-line me-1"></i>Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- === END add-agency-contact-modal === --}}
