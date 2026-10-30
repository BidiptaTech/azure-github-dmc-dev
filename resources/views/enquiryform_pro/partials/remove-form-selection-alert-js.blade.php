{{-- Reusable advanced alert for remove form selection - enquiryform_pro edit --}}
{{-- Pass hasNegotiationHistory (bool) to show appropriate message --}}
{{-- Pass hasExistingItems (bool) - only show popup when removing EXISTING (saved) items; new items removed silently --}}
<style>
/* Remove Form Selection Modal - Advanced UI/UX */
.swal2-remove-form-modal {
    width: 400px !important;
    padding: 0 !important;
    overflow: hidden;
    border-radius: 16px !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
    border: none;
}
.swal2-container.remove-form-modal-backdrop {
    backdrop-filter: blur(6px);
}
.swal2-remove-form-modal .swal2-icon {
    display: none !important;
}
.swal2-remove-form-modal .swal2-title {
    display: none;
}
.swal2-remove-form-modal .remove-form-modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.3;
    margin: 0 0 0.5rem;
    text-align: center;
}
.swal2-remove-form-modal .swal2-html-container {
    padding: 1.5rem 1.5rem 1.25rem;
    margin: 0 !important;
    font-size: 0.9375rem;
    line-height: 1.6;
    color: #64748b;
}
.swal2-remove-form-modal .swal2-html-container strong {
    color: #334155;
    font-weight: 600;
}
.swal2-remove-form-modal .swal2-html-container .remove-form-info-note {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-top: 0.75rem;
    padding: 0.75rem;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    border-radius: 10px;
    border: 1px solid rgba(251, 146, 60, 0.25);
    font-size: 0.8125rem;
    line-height: 1.5;
    color: #9a3412;
}
.swal2-remove-form-modal .swal2-html-container .remove-form-info-note i {
    font-size: 1rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}
.swal2-remove-form-modal .swal2-actions {
    padding: 1rem 1.5rem 1.5rem;
    gap: 0.75rem;
    flex-direction: row-reverse;
}
.swal2-remove-form-modal .swal2-styled.swal2-confirm {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
    color: #fff !important;
    border: none;
    padding: 0.625rem 1.25rem !important;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.35);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.swal2-remove-form-modal .swal2-styled.swal2-confirm:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}
.swal2-remove-form-modal .swal2-styled.swal2-cancel {
    background: #f1f5f9 !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0;
    padding: 0.625rem 1.25rem !important;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 10px;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.swal2-remove-form-modal .swal2-styled.swal2-cancel:hover {
    background: #e2e8f0 !important;
    color: #334155 !important;
    border-color: #cbd5e1;
}
.swal2-remove-form-modal .remove-form-modal-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
}
.swal2-remove-form-modal .remove-form-modal-icon i {
    font-size: 1.75rem;
    color: #d97706;
}
@keyframes remove-form-modal-in {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
.swal2-remove-form-modal.swal2-show {
    animation: remove-form-modal-in 0.2s ease-out;
}
</style>
<script>
function showRemoveFormSelectionAlert(count, itemTypeLabel, callback, hasNegotiationHistory, hasExistingItems) {
    // Only show confirmation when removing EXISTING (saved) items; new items removed silently
    if (hasExistingItems === false) {
        if (typeof callback === 'function') callback();
        return;
    }

    const itemText = count === 1 ? itemTypeLabel : count + ' ' + itemTypeLabel;
    const hasNegotiation = hasNegotiationHistory === true;

    const iconHtml = `<div class="remove-form-modal-icon"><i class="ri-delete-bin-line"></i></div>`;
    const titleHtml = `<h2 class="remove-form-modal-title">Remove ${itemText}?</h2>`;
    const msgWithNegotiation = `${iconHtml}${titleHtml}
        <p class="mb-0">Remove <strong>${itemText}</strong> from the form?</p>
        <div class="remove-form-info-note"><i class="ri-information-line"></i><span>Tour status may revert to <strong>New Enquiry</strong> and payment details will be cleared. <strong>Cannot be undone.</strong></span></div>`;
    const msgNoNegotiation = `${iconHtml}${titleHtml}
        <p class="mb-0">Remove <strong>${itemText}</strong> from the form? <strong>This cannot be undone.</strong></p>`;

    Swal.fire({
        title: '',
        html: hasNegotiation ? msgWithNegotiation : msgNoNegotiation,
        icon: null,
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel',
        width: '400px',
        padding: '0',
        customClass: {
            popup: 'swal2-remove-form-modal',
            title: 'swal2-remove-form-title',
            htmlContainer: 'swal2-remove-form-html',
            actions: 'swal2-remove-form-actions',
            confirmButton: 'swal2-remove-form-confirm',
            cancelButton: 'swal2-remove-form-cancel'
        },
        backdrop: 'rgba(15, 23, 42, 0.45)',
        didOpen: () => document.querySelector('.swal2-container')?.classList.add('remove-form-modal-backdrop'),
        didClose: () => document.querySelector('.swal2-container')?.classList.remove('remove-form-modal-backdrop')
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') callback();
    });
}
</script>
