{{-- Shared voucher rendering + online reject/cancel helpers for confirmed/definite/actual --}}
<script>
(function () {
    var GET_ATTRACTION_DATA_URL = @json(url('/booking/get-attraction-data'));
    var REJECT_ATTRACTION_URL = @json(url('/booking/reject-attraction-booking'));

    function escapeAttractionVoucherHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
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

    function extractPrimaryVoucherCode(source) {
        if (!source) {
            return '';
        }
        var top = String(source.voucher_code || '').trim();
        if (top) {
            return top;
        }
        var list = attractionVoucherList(source);
        for (var i = 0; i < list.length; i++) {
            var code = String(list[i].code || list[i].voucher_code || '').trim();
            if (code) {
                return code;
            }
        }
        return '';
    }

    function notifyAttraction(message, type) {
        type = type || 'info';
        if (typeof window.showToast === 'function') {
            window.showToast(message, type === 'danger' ? 'error' : type);
            return;
        }
        if (typeof toastr !== 'undefined') {
            if (type === 'success' && toastr.success) {
                toastr.success(message);
            } else if ((type === 'danger' || type === 'error') && toastr.error) {
                toastr.error(message);
            } else if (toastr.info) {
                toastr.info(message);
            } else {
                alert(message);
            }
            return;
        }
        alert(message);
    }

    function renderVoucherCancelResultHtml(payload) {
        var cancel = (payload && payload.data && payload.data.voucher_cancel) ? payload.data.voucher_cancel : null;
        var code = (payload && payload.data && payload.data.voucher_code)
            ? String(payload.data.voucher_code)
            : '';
        var providerMsg = cancel && cancel.message ? String(cancel.message) : '';
        var providerStatus = cancel && cancel.provider_status != null ? String(cancel.provider_status) : '';
        var headline = (payload && payload.message) ? String(payload.message) : 'Attraction booking updated.';

        return '' +
            '<div class="border-0 rounded-3 p-3 mb-0" style="background: linear-gradient(135deg, #e8f8ef 0%, #f0fff6 100%);">' +
                '<div class="d-flex align-items-start">' +
                    '<div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:42px;height:42px;">' +
                        '<i class="ri-checkbox-circle-line fs-4"></i>' +
                    '</div>' +
                    '<div class="flex-grow-1">' +
                        '<div class="fw-semibold text-success mb-1">Cancellation completed</div>' +
                        '<div class="text-dark mb-2">' + escapeAttractionVoucherHtml(headline) + '</div>' +
                        (code
                            ? '<div class="small text-muted mb-1">Voucher code</div><div class="fw-bold mb-2">' + escapeAttractionVoucherHtml(code) + '</div>'
                            : '') +
                        (providerMsg
                            ? '<div class="small text-muted mb-1">Provider response</div><div class="border rounded-2 bg-white px-3 py-2 small">' +
                              escapeAttractionVoucherHtml(providerMsg) +
                              (providerStatus ? ' <span class="badge bg-light text-muted border ms-1">Status ' + escapeAttractionVoucherHtml(providerStatus) + '</span>' : '') +
                              '</div>'
                            : '') +
                    '</div>' +
                '</div>' +
            '</div>';
    }

    function showRejectResultInModal(form, payload) {
        if (!form) {
            return;
        }
        var host = form.parentElement || form;
        var existing = host.querySelector('.attraction-voucher-cancel-result');
        if (existing) {
            existing.remove();
        }
        var wrap = document.createElement('div');
        wrap.className = 'attraction-voucher-cancel-result mb-3';
        wrap.innerHTML = renderVoucherCancelResultHtml(payload);
        host.insertBefore(wrap, form);
        form.classList.add('d-none');
        var footerBtn = form.closest('.modal') ? form.closest('.modal').querySelector('.btn-danger') : null;
        if (footerBtn) {
            footerBtn.classList.add('d-none');
        }
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

    window.isOnlineAttractionBooking = function (booking) {
        if (!booking || typeof booking !== 'object') {
            return false;
        }
        return !!(
            booking.is_online_attraction ||
            booking.order_type === 'online' ||
            booking.attractionSourceType === 'online' ||
            booking.attraction_source_type === 'online'
        );
    };

    /**
     * Approved Attraction Details actions: status pill + Mail Preview + View Files
     * (+ Cancel Voucher for online bookings that have a voucher code).
     */
    window.buildApprovedAttractionActionsHtml = function (tourId, attractionOrderIndex, bookingIndex, booking) {
        booking = booking || {};
        var referenceId = booking.referenceId || booking.reference_id || '';
        var displayDueDate = booking.displayDueDate || booking.display_due_date || '';
        var isOnline = window.isOnlineAttractionBooking(booking);
        var voucherCode = extractPrimaryVoucherCode(booking);
        var html = '' +
            '<span class="svc-status-pill is-approved">' +
                '<i class="ri-check-line"></i> Approved' +
                (referenceId ? (' · Ref: ' + escapeAttractionVoucherHtml(referenceId)) : '') +
                (displayDueDate ? (' · Due: ' + escapeAttractionVoucherHtml(displayDueDate)) : '') +
            '</span>' +
            '<button type="button" class="btn btn-sm svc-btn" style="border:1px solid #0ea5e9;color:#0369a1;" ' +
                'onclick="openAttractionMailPreview(' + tourId + ', ' + attractionOrderIndex + ', ' + bookingIndex + ')">' +
                '<i class="ri-mail-line me-1"></i>Mail Preview</button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary svc-btn" ' +
                'onclick="openAttractionFilesModal(\'' + tourId + '\', \'' + attractionOrderIndex + '\', \'' + bookingIndex + '\')" ' +
                'title="View and manage uploaded files">' +
                '<i class="ri-file-list-3-line me-1"></i>View Files</button>';
        if (isOnline && voucherCode) {
            html += '' +
                '<button type="button" class="btn btn-sm btn-outline-danger svc-btn" ' +
                    'onclick="window.openCancelAttractionVoucher(' + tourId + ', ' + attractionOrderIndex + ', ' + bookingIndex + ')" ' +
                    'title="Cancel this online attraction voucher with the supplier">' +
                    '<i class="ri-close-circle-line me-1"></i>Cancel Voucher</button>';
        }
        return html;
    };

    /**
     * Open cancel-voucher modal (approved online attractions). Prefills voucher code.
     */
    window.openCancelAttractionVoucher = function (tourId, attractionOrderIndex, bookingIndex, autoCancelDate) {
        window.__attractionCancelVoucherMode = true;
        try {
            if (typeof createAndShowIndividualAttractionModal === 'function') {
                createAndShowIndividualAttractionModal(
                    tourId,
                    attractionOrderIndex,
                    bookingIndex,
                    'reject',
                    autoCancelDate || null
                );
                return;
            }
            if (typeof window.createAndShowIndividualAttractionModal === 'function') {
                window.createAndShowIndividualAttractionModal(
                    tourId,
                    attractionOrderIndex,
                    bookingIndex,
                    'reject',
                    autoCancelDate || null
                );
                return;
            }
            if (typeof window.rejectIndividualAttraction === 'function') {
                window.rejectIndividualAttraction(tourId, attractionOrderIndex, bookingIndex, autoCancelDate || null);
                return;
            }
            notifyAttraction('Unable to open cancel voucher modal.', 'danger');
            window.__attractionCancelVoucherMode = false;
        } catch (err) {
            console.error('openCancelAttractionVoucher failed', err);
            window.__attractionCancelVoucherMode = false;
            notifyAttraction('Unable to open cancel voucher modal.', 'danger');
        }
    };

    /**
     * Insert voucher code + download card into Attraction Details modal (online only).
     */
    window.mountAttractionDetailsVouchers = function (modalContentEl, booking, opts) {
        if (!modalContentEl || !booking) {
            return;
        }
        opts = opts || {};
        var existing = modalContentEl.querySelector('.attraction-details-voucher-wrap');
        if (existing) {
            existing.remove();
        }
        if (!window.isOnlineAttractionBooking(booking)) {
            return;
        }

        var list = attractionVoucherList(booking.vouchers || booking);
        var code = extractPrimaryVoucherCode(booking);
        var download = String(
            booking.voucher_download_link ||
            booking.voucherDownloadLink ||
            ''
        ).trim();
        if (!list.length && (code || download)) {
            list = [{
                code: code,
                download_link: download,
                voucher: download
            }];
        }
        if (!list.length) {
            return;
        }

        var wrap = document.createElement('div');
        wrap.className = 'attraction-details-voucher-wrap mt-3 mb-0';
        wrap.innerHTML = window.renderAttractionVouchersHtml(list);

        var tourId = opts.tourId;
        var attractionOrderIndex = opts.attractionOrderIndex;
        var bookingIndex = opts.bookingIndex;
        if (code && tourId != null && attractionOrderIndex != null && bookingIndex != null) {
            var cancelBar = document.createElement('div');
            cancelBar.className = 'mt-2 d-flex justify-content-end';
            cancelBar.innerHTML = '' +
                '<button type="button" class="btn btn-outline-danger btn-sm" ' +
                    'onclick="window.openCancelAttractionVoucher(' + tourId + ', ' + attractionOrderIndex + ', ' + bookingIndex + ')">' +
                    '<i class="ri-close-circle-line me-1"></i>Cancel Voucher</button>';
            wrap.appendChild(cancelBar);
        }

        var actionsSection = null;
        modalContentEl.querySelectorAll('.svc-section').forEach(function (section) {
            var title = section.querySelector('.svc-section-title');
            if (title && /booking\s*actions/i.test(title.textContent || '')) {
                actionsSection = section;
            }
        });
        if (actionsSection && actionsSection.parentNode) {
            actionsSection.parentNode.insertBefore(wrap, actionsSection);
        } else {
            modalContentEl.appendChild(wrap);
        }
    };

    // Reject / cancel-voucher modal: voucher code only for online attraction bookings
    window.generateRejectAttractionForm = function (tourId, attractionOrderIndex, bookingIndex) {
        var isCancelVoucher = !!window.__attractionCancelVoucherMode;
        var alertTitle = isCancelVoucher ? 'Cancel Online Voucher' : 'Confirm Rejection';
        var alertBody = isCancelVoucher
            ? 'This will cancel the voucher with the supplier and remove this online attraction booking from the tour.'
            : 'This action will permanently reject and remove this attraction booking from the tour.';
        var reasonPlaceholder = isCancelVoucher
            ? 'Please provide a clear reason for cancelling this voucher...'
            : 'Please provide a clear reason for rejecting this attraction booking...';

        return '' +
            '<form id="rejectIndividualAttractionForm_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex + '">' +
                '<input type="hidden" name="tour_id" value="' + tourId + '">' +
                '<input type="hidden" name="attraction_order_index" value="' + attractionOrderIndex + '">' +
                '<input type="hidden" name="booking_index" value="' + bookingIndex + '">' +
                '<input type="hidden" name="attraction_order_id" id="rejectAttractionOrderId_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex + '" value="">' +
                '<div class="alert alert-danger border-0 mb-4" style="background: linear-gradient(45deg, #f8d7da, #f5c6cb); border-radius: 12px;">' +
                    '<div class="d-flex align-items-center">' +
                        '<i class="ri-error-warning-line me-2 text-danger fs-4"></i>' +
                        '<div>' +
                            '<strong class="text-danger">' + escapeAttractionVoucherHtml(alertTitle) + '</strong>' +
                            '<p class="mb-0 text-muted small mt-1">' + escapeAttractionVoucherHtml(alertBody) + '</p>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div id="rejectOnlineAttractionVoucherWrap_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex + '" class="mb-3 d-none">' +
                    '<div class="card border-0 shadow-sm" style="border-radius: 12px;">' +
                        '<div class="card-body p-3">' +
                            '<label for="rejectVoucherCode_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex + '" class="form-label fw-semibold mb-1">' +
                                '<i class="ri-coupon-3-line me-1 text-primary"></i>Voucher Code <span class="text-danger">*</span>' +
                            '</label>' +
                            '<input type="text" class="form-control form-control-lg" ' +
                                'id="rejectVoucherCode_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex + '" ' +
                                'name="voucher_code" autocomplete="off" ' +
                                'placeholder="Voucher code from provider" ' +
                                'style="border-radius: 8px; border: 2px solid #e9ecef;">' +
                            '<div class="form-text">Required to cancel this online attraction voucher with the supplier.</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="mb-3">' +
                    '<label for="rejectReason_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex + '" class="form-label fw-semibold">' +
                        '<i class="ri-message-line me-2"></i>Reason for Cancellation <span class="text-danger">*</span>' +
                    '</label>' +
                    '<textarea class="form-control form-control-lg" id="rejectReason_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex + '" ' +
                        'name="reject_reason" required rows="4" ' +
                        'placeholder="' + escapeAttractionVoucherHtml(reasonPlaceholder) + '" ' +
                        'style="border-radius: 8px; border: 2px solid #e9ecef;" minlength="10" maxlength="1000"></textarea>' +
                    '<div class="form-text">Provide a detailed explanation (10-1000 characters)</div>' +
                '</div>' +
            '</form>';
    };

    window.hydrateAttractionRejectForm = function (tourId, attractionOrderIndex, bookingIndex, attractionOrderId) {
        var wrap = document.getElementById('rejectOnlineAttractionVoucherWrap_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex);
        var codeInput = document.getElementById('rejectVoucherCode_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex);
        var orderIdInput = document.getElementById('rejectAttractionOrderId_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex);
        if (!wrap || !codeInput) {
            return;
        }

        fetch(GET_ATTRACTION_DATA_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: JSON.stringify({
                tour_id: tourId,
                attraction_order_index: attractionOrderIndex,
                booking_index: bookingIndex,
                attraction_order_id: attractionOrderId || undefined
            })
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data || !data.success || !data.data || !data.data.attraction_booking) {
                return;
            }
            var booking = data.data.attraction_booking;
            var isOnline = !!(
                booking.is_online_attraction ||
                booking.order_type === 'online' ||
                booking.attractionSourceType === 'online'
            );
            if (orderIdInput && booking.booking_id) {
                orderIdInput.value = booking.booking_id;
            }
            if (!isOnline) {
                wrap.classList.add('d-none');
                codeInput.removeAttribute('required');
                codeInput.value = '';
                return;
            }
            var code = extractPrimaryVoucherCode(booking);
            var isApproved = !!booking.is_approve;
            // Show voucher field only for online bookings that have (or need) a voucher cancel code
            if (!code && !isApproved) {
                wrap.classList.add('d-none');
                codeInput.removeAttribute('required');
                codeInput.value = '';
                return;
            }
            wrap.classList.remove('d-none');
            codeInput.value = code;
            codeInput.setAttribute('required', 'required');
            codeInput.readOnly = !!code;
        })
        .catch(function (err) {
            console.error('Failed to hydrate attraction reject form', err);
        });
    };

    window.confirmIndividualAttractionRejection = function (tourId, attractionOrderIndex, bookingIndex) {
        try {
            var form = document.getElementById('rejectIndividualAttractionForm_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex);
            if (!form) {
                console.error('Individual attraction reject form not found');
                return;
            }

            var reasonEl = document.getElementById('rejectReason_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex);
            var codeEl = document.getElementById('rejectVoucherCode_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex);
            var orderIdEl = document.getElementById('rejectAttractionOrderId_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex);
            var voucherWrap = document.getElementById('rejectOnlineAttractionVoucherWrap_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex);
            var rejectReason = reasonEl ? String(reasonEl.value || '') : '';
            var voucherCode = codeEl ? String(codeEl.value || '').trim() : '';
            var isOnlineVoucherVisible = voucherWrap && !voucherWrap.classList.contains('d-none');
            var voucherRequired = !!(codeEl && codeEl.hasAttribute('required') && isOnlineVoucherVisible);

            if (rejectReason.trim().length < 10) {
                notifyAttraction('Please provide a cancellation reason (at least 10 characters).', 'warning');
                return;
            }
            if (rejectReason.trim().length > 1000) {
                notifyAttraction('Rejection reason is too long (maximum 1000 characters).', 'warning');
                return;
            }
            if (voucherRequired && !voucherCode) {
                notifyAttraction('Voucher code is required to cancel this online attraction booking.', 'warning');
                if (codeEl) {
                    codeEl.focus();
                }
                return;
            }

            var confirmMsg = voucherRequired && voucherCode
                ? 'Cancel this online attraction voucher with the supplier and reject the booking? This cannot be undone.'
                : 'Are you sure you want to reject this attraction booking? This action cannot be undone.';
            if (!confirm(confirmMsg)) {
                return;
            }

            var submitButton = form.closest('.modal') ? form.closest('.modal').querySelector('.btn-danger') : null;
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="ri-loader-4-line me-2"></i>Processing...';
            }

            var requestData = {
                tour_id: tourId,
                attraction_order_index: attractionOrderIndex,
                booking_index: bookingIndex,
                cancel_reason: rejectReason.trim()
            };
            if (voucherCode) {
                requestData.voucher_code = voucherCode;
            }
            if (orderIdEl && orderIdEl.value) {
                requestData.attraction_order_id = parseInt(orderIdEl.value, 10) || orderIdEl.value;
            }

            fetch(REJECT_ATTRACTION_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData)
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, status: response.status, data: data };
                });
            })
            .then(function (result) {
                var data = result.data || {};
                if (data.success) {
                    var hasVoucherCancel = !!(data.data && data.data.voucher_cancel);
                    if (hasVoucherCancel) {
                        showRejectResultInModal(form, data);
                        notifyAttraction(data.message || 'Online attraction voucher cancelled successfully.', 'success');
                        setTimeout(function () {
                            var modalId = 'individualAttractionModal_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex + '_reject';
                            if (typeof closeIndividualAttractionModal === 'function') {
                                closeIndividualAttractionModal(modalId);
                            }
                            location.reload();
                        }, 2200);
                    } else {
                        notifyAttraction(data.message || 'Attraction booking rejected successfully.', 'success');
                        var modalId = 'individualAttractionModal_' + tourId + '_' + attractionOrderIndex + '_' + bookingIndex + '_reject';
                        if (typeof closeIndividualAttractionModal === 'function') {
                            closeIndividualAttractionModal(modalId);
                        }
                        location.reload();
                    }
                    return;
                }

                var errMsg = (data && data.message) ? data.message : 'Failed to reject attraction booking.';
                if (data.data && data.data.voucher_cancel && data.data.voucher_cancel.message) {
                    errMsg = data.data.voucher_cancel.message;
                }
                notifyAttraction(errMsg, 'danger');
            })
            .catch(function (error) {
                console.error('Error rejecting attraction booking:', error);
                notifyAttraction('Error rejecting attraction booking. Please try again.', 'danger');
            })
            .finally(function () {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="ri-close-line me-2"></i>Confirm Rejection';
                }
            });
        } catch (error) {
            console.error('Error in confirmIndividualAttractionRejection:', error);
            notifyAttraction('Error processing rejection. Please try again.', 'danger');
        }
    };

    // Auto-hydrate voucher code when reject / cancel-voucher modal is shown
    document.addEventListener('shown.bs.modal', function (e) {
        var modal = e.target;
        if (!modal || !modal.id) {
            return;
        }
        var match = modal.id.match(/^individualAttractionModal_(\d+)_(\d+)_(\d+)_reject$/);
        if (!match) {
            return;
        }
        if (window.__attractionCancelVoucherMode) {
            var title = modal.querySelector('.modal-title');
            if (title) {
                title.textContent = 'Cancel Attraction Voucher';
            }
            var subtitle = modal.querySelector('.modal-header .text-white-50, .modal-header .small');
            if (subtitle && subtitle.classList.contains('text-white-50')) {
                subtitle.textContent = 'Cancel voucher with supplier, then remove this booking';
            }
            var dangerBtn = modal.querySelector('.modal-footer .btn-danger');
            if (dangerBtn) {
                dangerBtn.innerHTML = '<i class="ri-close-circle-line me-2"></i>Confirm Cancel Voucher';
            }
            window.__attractionCancelVoucherMode = false;
        }
        window.hydrateAttractionRejectForm(
            parseInt(match[1], 10),
            parseInt(match[2], 10),
            parseInt(match[3], 10)
        );
    });
})();
</script>
