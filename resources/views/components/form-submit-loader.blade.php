@props([
    'message' => 'Saving...',
])

<style>
    .form-submit-loader {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .form-submit-loader.active {
        display: flex;
    }

    .form-submit-loader__box {
        background: #fff;
        border-radius: 0.5rem;
        padding: 1.25rem 1.5rem;
        min-width: 180px;
        text-align: center;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
    }
</style>

<div id="formSubmitLoader" class="form-submit-loader" aria-live="polite" aria-busy="false">
    <div class="form-submit-loader__box">
        <div class="spinner-border text-primary mb-2" role="status" aria-hidden="true"></div>
        <div class="fw-semibold form-submit-loader__text">{{ $message }}</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const loader = document.getElementById('formSubmitLoader');
    if (!loader) return;

    const defaultMessage = @json($message);

    function showFormSubmitLoader(btn, message) {
        const textEl = loader.querySelector('.form-submit-loader__text');
        if (textEl) textEl.textContent = message || defaultMessage;

        loader.classList.add('active');
        loader.setAttribute('aria-busy', 'true');

        if (!btn) return;
        btn.disabled = true;

        const label = btn.querySelector('.js-submit-loader-btn-text');
        const loading = btn.querySelector('.js-submit-loader-btn-loading');
        if (label) label.classList.add('d-none');
        if (loading) loading.classList.remove('d-none');
    }

    window.showFormSubmitLoader = showFormSubmitLoader;

    document.querySelectorAll('form.js-submit-loader-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const btn = form.querySelector('.js-submit-loader-btn') || form.querySelector('button[type="submit"]:not([disabled])');
            const message = form.getAttribute('data-loader-message') || defaultMessage;

            // Wait for other submit handlers (validation) to call preventDefault if needed
            setTimeout(function () {
                if (e.defaultPrevented) return;
                showFormSubmitLoader(btn, message);
            }, 0);
        });
    });
});
</script>
