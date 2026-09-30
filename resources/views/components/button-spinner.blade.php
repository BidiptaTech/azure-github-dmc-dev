@props([
    'type' => 'submit',
    'label' => 'Save',
    'loadingText' => 'Saving...',
    'id' => 'btnSpinner_' . uniqid(),
])

<button type="{{ $type }}" id="{{ $id }}" {{ $attributes->merge(['class' => 'btn btn-primary px-4']) }}>
    <span class="btn-spinner-icon spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
    <span class="btn-spinner-label">{{ $slot->isEmpty() ? $label : $slot }}</span>
</button>

<script>
(function () {
    const btn = document.getElementById(@json($id));
    if (!btn) return;

    const icon = btn.querySelector('.btn-spinner-icon');
    const label = btn.querySelector('.btn-spinner-label');
    const loadingText = @json($loadingText);
    const originalLabel = label ? label.textContent : @json($label);
    const form = btn.closest('form');
    if (!form) return;

    function resetSpinner() {
        if (icon) icon.classList.add('d-none');
        if (label) label.textContent = originalLabel;
        btn.disabled = false;
    }

    // Expose so page scripts can unlock the button after client-side validation fails.
    btn.resetSubmitSpinner = resetSpinner;
    window.resetFormSubmitSpinners = window.resetFormSubmitSpinners || function (formEl) {
        const root = formEl || document;
        root.querySelectorAll('button[type="submit"]').forEach(function (b) {
            if (typeof b.resetSubmitSpinner === 'function') {
                b.resetSubmitSpinner();
            }
        });
        const overlay = document.getElementById('formSubmitLoader');
        if (overlay) {
            overlay.classList.remove('active');
            overlay.setAttribute('aria-busy', 'false');
        }
    };

    // The native "submit" event only fires after HTML5 validation passes,
    // so the spinner won't show when required fields are missing.
    form.addEventListener('submit', function (e) {
        if (icon) icon.classList.remove('d-none');
        if (label) label.textContent = loadingText;

        // Defer so other handlers can preventDefault (client validation).
        setTimeout(function () {
            if (e.defaultPrevented) {
                resetSpinner();
                return;
            }
            btn.disabled = true;
        }, 0);
    });
})();
</script>
