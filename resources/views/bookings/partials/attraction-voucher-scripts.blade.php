{{-- Shared voucher rendering for confirmed/definite/actual attraction approval modals --}}
<script>
(function () {
    function escapeAttractionVoucherHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function attractionVoucherList(source) {
        if (!source) {
            return [];
        }
        if (Array.isArray(source)) {
            return source.filter(function (row) { return row && typeof row === 'object'; });
        }
        if (Array.isArray(source.vouchers)) {
            return source.vouchers.filter(function (row) { return row && typeof row === 'object'; });
        }
        if (source.order_details && Array.isArray(source.order_details.vouchers)) {
            return source.order_details.vouchers.filter(function (row) { return row && typeof row === 'object'; });
        }
        return [];
    }

    function isAuthenticatedAttractionVoucherUrl(url) {
        const value = String(url || '').trim().toLowerCase();
        if (!value) {
            return false;
        }
        return value.indexOf('/voucher/download') !== -1
            || value.indexOf('api.attractionsg.com') !== -1
            || value.indexOf('tdpapi.attractionsg.com') !== -1;
    }

    function attractionVoucherDownloadUrl(voucher) {
        if (!voucher || typeof voucher !== 'object') {
            return '';
        }
        const publicUrl = String(voucher.voucher || voucher.voucher_url || voucher.voucher_image || '').trim();
        const download = String(voucher.download_link || voucher.download_url || '').trim();
        if (publicUrl && !isAuthenticatedAttractionVoucherUrl(publicUrl)) {
            return publicUrl;
        }
        if (download && !isAuthenticatedAttractionVoucherUrl(download)) {
            return download;
        }
        return publicUrl || download;
    }

    function attractionVoucherImageUrl(voucher) {
        if (!voucher || typeof voucher !== 'object') {
            return '';
        }
        const imageUrl = String(voucher.voucher || voucher.voucher_url || voucher.voucher_image || '').trim();
        if (imageUrl && !isAuthenticatedAttractionVoucherUrl(imageUrl)) {
            return imageUrl;
        }
        return '';
    }

    window.renderAttractionVouchersHtml = function (vouchers) {
        const list = attractionVoucherList(vouchers);
        if (!list.length) {
            return '';
        }

        const cards = list.map(function (voucher, index) {
            const code = String(voucher.code || voucher.voucher_code || '').trim();
            const imageUrl = attractionVoucherImageUrl(voucher);
            const downloadUrl = attractionVoucherDownloadUrl(voucher);
            const from = voucher.valid_date_from ? String(voucher.valid_date_from) : '';
            const to = voucher.valid_date_to ? String(voucher.valid_date_to) : '';
            const qty = voucher.quantity != null ? String(voucher.quantity) : '';
            const item = String(voucher.item || voucher.ticket_name || voucher.attraction_title || '').trim();
            const label = list.length > 1 ? ('Voucher ' + (index + 1)) : 'Voucher';

            return '' +
                '<div class="border rounded-3 p-3 mb-3 bg-white">' +
                    '<div class="d-flex justify-content-between align-items-start mb-2">' +
                        '<h6 class="mb-0 fw-bold">' + escapeAttractionVoucherHtml(label) + '</h6>' +
                        (qty ? '<small class="text-muted">Qty: ' + escapeAttractionVoucherHtml(qty) + '</small>' : '') +
                    '</div>' +
                    (item ? '<div class="small text-muted mb-2">' + escapeAttractionVoucherHtml(item) + '</div>' : '') +
                    '<div class="mb-2"><span class="text-muted small d-block">Voucher Code</span>' +
                        '<div class="fw-bold fs-5">' + (code ? escapeAttractionVoucherHtml(code) : '—') + '</div>' +
                    '</div>' +
                    ((from || to) ? '<div class="small text-muted mb-2">Valid: ' +
                        escapeAttractionVoucherHtml(from || '—') + ' → ' + escapeAttractionVoucherHtml(to || '—') +
                    '</div>' : '') +
                    (imageUrl
                        ? '<div class="mb-3 text-center"><img src="' + escapeAttractionVoucherHtml(imageUrl) +
                          '" alt="Voucher" class="img-fluid rounded border" style="max-height: 280px; object-fit: contain;" onerror="this.style.display=\'none\'"></div>'
                        : '') +
                    (downloadUrl
                        ? '<a href="' + escapeAttractionVoucherHtml(downloadUrl) +
                          '" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer" download="' +
                          escapeAttractionVoucherHtml(code ? ('voucher-' + code) : 'voucher') + '">' +
                          '<i class="ri-download-2-line me-1"></i>Download Voucher</a>'
                        : '<div class="small text-muted">Download link is not available for this voucher.</div>') +
                '</div>';
        }).join('');

        return '' +
            '<div class="card border-0 shadow-sm mb-0" style="border-radius: 12px;">' +
                '<div class="card-header bg-light border-0 py-3">' +
                    '<h6 class="mb-0 fw-bold text-dark d-flex align-items-center">' +
                        '<i class="ri-coupon-3-line me-2 text-primary"></i>Voucher' +
                    '</h6>' +
                '</div>' +
                '<div class="card-body p-4">' + cards + '</div>' +
            '</div>';
    };

    window.ensureAttractionVoucherWrap = function (tourId, attractionOrderIndex, bookingIndex) {
        const id = 'onlineAttractionVoucherWrap_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex;
        let el = document.getElementById(id);
        if (el) {
            return el;
        }
        const form = document.getElementById('approveIndividualAttractionForm_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex);
        if (!form) {
            return null;
        }
        el = document.createElement('div');
        el.id = id;
        el.className = 'mb-3';
        const infoCard = form.querySelector('.card');
        if (infoCard && infoCard.parentNode) {
            infoCard.parentNode.insertBefore(el, infoCard.nextSibling);
        } else {
            form.insertBefore(el, form.firstChild ? form.firstChild.nextSibling : null);
        }
        return el;
    };

    window.mountAttractionVouchers = function (tourId, attractionOrderIndex, bookingIndex, vouchers) {
        const list = attractionVoucherList(vouchers);
        const wrap = window.ensureAttractionVoucherWrap(tourId, attractionOrderIndex, bookingIndex);
        if (!wrap) {
            return;
        }
        if (!list.length) {
            wrap.innerHTML = '';
            wrap.classList.add('d-none');
            return;
        }
        wrap.classList.remove('d-none');
        wrap.innerHTML = window.renderAttractionVouchersHtml(list);
    };
})();
</script>
