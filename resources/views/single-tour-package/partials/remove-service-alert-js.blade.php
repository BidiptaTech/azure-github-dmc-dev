{{-- Reusable advanced alert for remove service - include in single-tour-package edit views --}}
{{-- Set window.removeServicePageTourStatus (tour's tour_status) and window.hasNegotiationHistory --}}
{{-- Logic matches bookings/partials/reject-service-alert-js: Definite/Actual = refunds note; else negotiation warning. --}}
<script>
function showRemoveServiceAlert(serviceType, callback, hasNegotiationHistory) {
    const labels = {
        'hotel': 'Hotel', 'restaurant': 'Restaurant', 'attraction': 'Attraction', 'guide': 'Guide', 'transport': 'Transport'
    };
    const serviceLabel = labels[serviceType] || serviceType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    const pageTourStatus = (window.removeServicePageTourStatus || '').trim();
    const isDefiniteOrActualPage = pageTourStatus === 'Definite' || pageTourStatus === 'Actual';
    const hasNegotiation = hasNegotiationHistory === true;

    const msgWithNegotiation = `<p class="mb-2" style="font-size: 0.875rem;">Remove this <strong>${serviceLabel}</strong> service?</p>
        <p class="mb-0 text-warning" style="font-size: 0.8rem; line-height: 1.4;"><i class="ri-information-line me-1"></i>Tour status may revert to <strong>New Enquiry</strong> and payment details will be cleared. Cannot be undone.</p>`;
    const msgNoNegotiation = `<p class="mb-0" style="font-size: 0.875rem;">Remove this <strong>${serviceLabel}</strong> service? This cannot be undone.</p>`;
    const msgRefundsStyle = `<p class="mb-2" style="font-size: 0.875rem;">Remove this <strong>${serviceLabel}</strong> service for refund / credit processing?</p>
        <p class="mb-0 text-primary" style="font-size: 0.8rem; line-height: 1.4;"><i class="ri-shield-check-line me-1"></i><strong>Tour status</strong> and <strong>payment details</strong> stay unchanged. Only this service is removed (refunds workflow). Cannot be undone.</p>`;

    let html;
    if (isDefiniteOrActualPage) {
        html = msgRefundsStyle;
    } else if (hasNegotiation) {
        html = msgWithNegotiation;
    } else {
        html = msgNoNegotiation;
    }

    Swal.fire({
        title: 'Remove ' + serviceLabel + '?',
        html: html,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel',
        width: '360px',
        padding: '1rem 1.25rem',
        customClass: { popup: 'rounded-2', confirmButton: 'px-3 py-2', cancelButton: 'px-3 py-2', title: 'fs-6' }
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') callback();
    });
}
</script>
