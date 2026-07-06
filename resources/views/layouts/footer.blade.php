<!-- Footer -->
<footer class="footer bg-light py-2 mt-4">
    <div class="container">
        @php
        $logoSetting = \App\Helpers\CommonHelper::masterSettingsName('logo');
        $fileStorage = \App\Helpers\CommonHelper::masterSettingsName('file_storage')['master_value']
        ?? 'local'; // Default to local if not set
        @endphp
        <div class="row align-items-center justify-content-center">
            <!-- Left Column -->
            <div class="col-12 col-md-6 text-center text-md-start">
                <div class="card-body p-2">
                    <h6 class="card-title mb-1" style="font-size: 0.875rem;">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> 
                        © <span>{{ \App\Helpers\CommonHelper::masterSettingsName('name')['master_value'] }}</span>  - Crafted by <strong class="text-uppercase">Coactive IT Solutions Pvt Ltd</strong>
                    </h6>
                </div>
            </div>

            <!-- Right Column with Links Styled as Buttons -->
            <div class="col-12 col-md-6 text-center text-md-end mt-2 mt-md-0">
                <div class="card-body p-0">
                    <a href="#" class="btn btn-outline-primary btn-sm me-2" style="font-size: 0.875rem;">About</a>
                    <a href="#" class="btn btn-outline-primary btn-sm me-2" style="font-size: 0.875rem;">Support</a>
                    <a href="#" class="btn btn-outline-primary btn-sm" style="font-size: 0.875rem;">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- / Footer -->

<!-- Core JS -->
{{-- Use config('app.url') (not env('APP_URL')): env() returns null when config is cached, which breaks
     these URLs — especially since APP_URL includes the subpath (/backadm-dmc) where public/ is served.
     config('app.url') is baked at config:cache time, so it always keeps the subpath. --}}
@php $assetBase = rtrim(config('app.url'), '/'); @endphp
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>window.jQuery || document.write('<script src="{{ $assetBase }}/assets/vendor/libs/jquery/jquery.js"><\/script>');</script>
<script src="{{ $assetBase }}/assets/vendor/libs/popper/popper.js"></script>
<script src="{{ $assetBase }}/assets/vendor/js/bootstrap.js"></script>
<script src="{{ $assetBase }}/assets/vendor/libs/node-waves/node-waves.js"></script>
<script src="{{ $assetBase }}/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="{{ $assetBase }}/assets/vendor/libs/hammer/hammer.js"></script>
<script src="{{ $assetBase }}/assets/vendor/libs/i18n/i18n.js"></script>
<script src="{{ $assetBase }}/assets/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="{{ $assetBase }}/assets/vendor/js/menu.js"></script>

<!-- Helpers -->
<script src="{{ $assetBase }}/assets/vendor/js/helpers.js"></script>
<script src="{{ $assetBase }}/assets/vendor/js/template-customizer.js"></script>
<script src="{{ $assetBase }}/assets/js/config.js"></script>

<!-- Vendors JS -->
<script src="{{ $assetBase }}/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<script src="{{ $assetBase }}/assets/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="{{ $assetBase }}/assets/vendor/libs/swiper/swiper.js"></script>

<!-- Main JS -->
<script src="{{ $assetBase }}/assets/js/main.js"></script>

<!-- Page JS -->
<script src="{{ $assetBase }}/assets/js/dashboards-crm.js"></script>

@auth
<script>
(function () {
    var loginUrl = @json(route('login'));
    var statusUrl = @json(route('user.account.status'));

    function redirectIfDeactivated(response) {
        if (response.status === 401) {
            window.location.href = loginUrl;
            return true;
        }
        if (response.ok) {
            return response.json().then(function (data) {
                if (data && data.active === false) {
                    window.location.href = loginUrl;
                    return true;
                }
            }).catch(function () {});
        }
        return false;
    }

    function pollAccountStatus() {
        fetch(statusUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(redirectIfDeactivated).catch(function () {});
    }

    if (typeof jQuery !== 'undefined') {
        jQuery(document).ajaxComplete(function (_event, xhr) {
            redirectIfDeactivated(xhr);
        });
    }

    setInterval(pollAccountStatus, 15000);

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            pollAccountStatus();
        }
    });

    window.addEventListener('focus', pollAccountStatus);
})();
</script>
@endauth
