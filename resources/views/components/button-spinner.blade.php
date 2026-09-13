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
    const form = btn.closest('form');
    if (!form) return;

    // The native "submit" event only fires after HTML5 validation passes,
    // so the spinner won't show when required fields are missing.
    form.addEventListener('submit', function () {
        if (icon) icon.classList.remove('d-none');
        if (label) label.textContent = loadingText;
        // Defer disabling so the submission is not interrupted in some browsers.
        setTimeout(function () { btn.disabled = true; }, 0);
    });
})();
</script>
