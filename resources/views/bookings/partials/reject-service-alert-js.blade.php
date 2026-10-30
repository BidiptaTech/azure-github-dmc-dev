{{-- Reusable advanced alert for reject service - include in booking views that have reject buttons --}}
{{-- Pass tourId to show message based on negotiation history (enquiry_comments) --}}
<style>
/* Reject Service Modal - Advanced UI/UX */
.swal2-reject-modal {
    width: 400px !important;
    padding: 0 !important;
    overflow: hidden;
    border-radius: 16px !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
    border: none;
}
.swal2-container.reject-modal-backdrop {
    backdrop-filter: blur(6px);
}
.swal2-reject-modal .swal2-icon {
    display: none !important;
}
.swal2-reject-modal .swal2-title {
    display: none;
}
.swal2-reject-modal .reject-modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.3;
    margin: 0 0 0.5rem;
    text-align: center;
}
.swal2-reject-modal .swal2-html-container {
    padding: 1.5rem 1.5rem 1.25rem;
    margin: 0 !important;
    font-size: 0.9375rem;
    line-height: 1.6;
    color: #64748b;
}
.swal2-reject-modal .swal2-html-container strong {
    color: #334155;
    font-weight: 600;
}
.swal2-reject-modal .swal2-html-container .reject-info-note {
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
.swal2-reject-modal .swal2-html-container .reject-info-note i {
    font-size: 1rem;
    margin-top: 0.125rem;
    flex-shrink: 0;
}
/* Refunds-style note (Definite/Actual): status & payment stay unchanged */
.swal2-reject-modal .swal2-html-container .reject-info-note.reject-info-note--refunds {
    background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%);
    border: 1px solid rgba(99, 102, 241, 0.3);
    color: #3730a3;
}
.swal2-reject-modal .swal2-html-container .reject-info-note.reject-info-note--refunds i {
    color: #4f46e5;
}
.swal2-reject-modal .swal2-actions {
    padding: 1rem 1.5rem 1.5rem;
    gap: 0.75rem;
    flex-direction: row-reverse;
}
.swal2-reject-modal .swal2-styled.swal2-confirm {
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
.swal2-reject-modal .swal2-styled.swal2-confirm:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}
.swal2-reject-modal .swal2-styled.swal2-cancel {
    background: #f1f5f9 !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0;
    padding: 0.625rem 1.25rem !important;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 10px;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.swal2-reject-modal .swal2-styled.swal2-cancel:hover {
    background: #e2e8f0 !important;
    color: #334155 !important;
    border-color: #cbd5e1;
}
.swal2-reject-modal .reject-modal-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%);
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(251, 146, 60, 0.2);
}
.swal2-reject-modal .reject-modal-icon i {
    font-size: 1.75rem;
    color: #ea580c;
}
@keyframes reject-modal-in {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
.swal2-reject-modal.swal2-show {
    animation: reject-modal-in 0.2s ease-out;
}
</style>
<script>
function showRejectServiceAlert(serviceType, callback, tourId) {
    const labels = {
        'hotel': 'Hotel', 'restaurant': 'Restaurant', 'attraction': 'Attraction', 'guide': 'Guide',
        'arrival': 'Arrival Transfer', 'departure': 'Departure Transfer', 'hourly': 'Hourly Transport',
        'point_to_point': 'Point-to-Point Transport', 'local_transport': 'Local Transport'
    };
    const serviceLabel = labels[serviceType] || serviceType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    const pageTourStatus = (window.rejectServicePageTourStatus || '').trim();
    const isDefiniteOrActualPage = pageTourStatus === 'Definite' || pageTourStatus === 'Actual';
    const hasNegotiation = tourId && (window.tourNegotiationHistory || {})[tourId] === true;

    const iconHtml = `<div class="reject-modal-icon"><i class="ri-error-warning-line"></i></div>`;
    const titleHtml = `<h2 class="reject-modal-title">Reject ${serviceLabel}?</h2>`;
    const msgWithNegotiation = `${iconHtml}${titleHtml}
        <p class="mb-0">Permanently reject this <strong>${serviceLabel}</strong> service?</p>
        <div class="reject-info-note"><i class="ri-information-line"></i><span>Tour status may revert to <strong>New Enquiry</strong> and payment details will be cleared. <strong>Cannot be undone.</strong></span></div>`;
    const msgNoNegotiation = `${iconHtml}${titleHtml}
        <p class="mb-0">Permanently reject this <strong>${serviceLabel}</strong> service? <strong>This cannot be undone.</strong></p>`;
    /* Same idea as refunds: only this service is rejected; tour & payment stay as-is */
    const msgRefundsStyle = `${iconHtml}${titleHtml}
        <p class="mb-0">Reject this <strong>${serviceLabel}</strong> service for refund / credit processing?</p>
        <div class="reject-info-note reject-info-note--refunds"><i class="ri-shield-check-line"></i><span><strong>Tour status</strong> and <strong>payment details</strong> stay unchanged. Only this service is rejected (refunds workflow). <strong>This cannot be undone.</strong></span></div>`;

    let html;
    if (isDefiniteOrActualPage) {
        html = msgRefundsStyle;
    } else if (hasNegotiation) {
        html = msgWithNegotiation;
    } else {
        html = msgNoNegotiation;
    }

    Swal.fire({
        title: '',
        html: html,
        icon: null,
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Reject',
        cancelButtonText: 'Cancel',
        width: '400px',
        padding: '0',
        customClass: {
            popup: 'swal2-reject-modal',
            title: 'swal2-reject-title',
            htmlContainer: 'swal2-reject-html',
            actions: 'swal2-reject-actions',
            confirmButton: 'swal2-reject-confirm',
            cancelButton: 'swal2-reject-cancel'
        },
        backdrop: 'rgba(15, 23, 42, 0.45)',
        didOpen: () => document.querySelector('.swal2-container')?.classList.add('reject-modal-backdrop'),
        didClose: () => document.querySelector('.swal2-container')?.classList.remove('reject-modal-backdrop')
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') callback();
    });
}
</script>
