{{-- Reusable advanced alert for remove service - include in single-tour-package edit views --}}
{{-- Pass hasNegotiationHistory (bool) to show appropriate message --}}
<script>
function showRemoveServiceAlert(serviceType, callback, hasNegotiationHistory) {
    const labels = {
        'hotel': 'Hotel', 'restaurant': 'Restaurant', 'attraction': 'Attraction', 'guide': 'Guide', 'transport': 'Transport'
    };
    const serviceLabel = labels[serviceType] || serviceType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    const hasNegotiation = hasNegotiationHistory === true;

    const msgWithNegotiation = `<p class="mb-2" style="font-size: 0.875rem;">Remove this <strong>${serviceLabel}</strong> service?</p>
        <p class="mb-0 text-warning" style="font-size: 0.8rem; line-height: 1.4;"><i class="ri-information-line me-1"></i>Tour status may revert to <strong>New Enquiry</strong> and payment details will be cleared. Cannot be undone.</p>`;
    const msgNoNegotiation = `<p class="mb-0" style="font-size: 0.875rem;">Remove this <strong>${serviceLabel}</strong> service? This cannot be undone.</p>`;

    Swal.fire({
        title: 'Remove ' + serviceLabel + '?',
        html: hasNegotiation ? msgWithNegotiation : msgNoNegotiation,
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
