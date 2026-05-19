@extends('layouts.layout')
@section('title', 'Lost & Found and Incident Management')

@push('css')
<style>
    .lf-page { background: #f1f5f9; min-height: 100vh; padding: 0.75rem 0 1.5rem; }
    .lf-header {
        background: #fff;
        border-radius: 0.375rem;
        border: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        margin-bottom: 0.75rem;
    }
    .lf-header h4 { font-size: 1rem; font-weight: 600; color: #334155; margin: 0; }
    .lf-card { border: 1px solid #e2e8f0; border-radius: 0.375rem; overflow: hidden; background: #fff; }
    .lf-table { font-size: 0.8125rem; margin: 0; }
    .lf-table thead th {
        background: rgb(6, 132, 216);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        padding: 0.5rem 0.65rem;
        white-space: nowrap;
        border: none;
    }
    .lf-table tbody td { padding: 0.5rem 0.65rem; vertical-align: middle; }
    .lf-badge-resolved { background: #dcfce7; color: #166534; }
    .lf-badge-open { background: #fef3c7; color: #92400e; }
    .lf-modal .form-label { font-size: 0.8125rem; font-weight: 600; color: #475569; }
    .lf-readonly-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        color: #334155;
        min-height: 2.5rem;
    }
    .lf-preview-img { max-width: 80px; max-height: 80px; object-fit: cover; border-radius: 0.25rem; margin: 0.25rem; border: 1px solid #e2e8f0; }
</style>
@endpush

@section('content')
<div class="container-fluid lf-page">
    <div class="lf-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4>Lost and Found Reports</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$dmcId)
        <div class="alert alert-warning">Unable to determine DMC for your account. Reports cannot be loaded.</div>
    @endif

    <div class="lf-card">
        <div class="table-responsive">
            <table class="table table-hover lf-table mb-0">
                <thead>
                    <tr>
                        <th>Tour Id</th>
                        <th>Subject</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Resolved</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->tour_display_id ?? $report->tour_id }}</td>
                            <td>{{ $report->subject }}</td>
                            <td>{{ $report->phone ?? '—' }}</td>
                            <td>{{ $report->email ?? '—' }}</td>
                            <td>
                                @if($report->resolved)
                                    <span class="badge lf-badge-resolved">Yes</span>
                                @else
                                    <span class="badge lf-badge-open">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-sm btn-primary lf-view-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#lostFoundModal"
                                        data-id="{{ $report->id }}"
                                        data-tour-id="{{ $report->tour_display_id ?? $report->tour_id }}"
                                        data-subject="{{ e($report->subject) }}"
                                        data-description="{{ e($report->description ?? '') }}">
                                    <i class="ri-eye-line me-1"></i> View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No lost & found reports found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Detail & response modal --}}
<div class="modal fade lf-modal" id="lostFoundModal" tabindex="-1" aria-labelledby="lostFoundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lostFoundModalLabel">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Tour Id: <strong id="lfModalTourId">—</strong></p>

                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <div class="lf-readonly-box" id="lfModalSubject">—</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <div class="lf-readonly-box" id="lfModalDescription" style="white-space: pre-wrap;">—</div>
                </div>

                <hr>

                <p class="text-muted small mb-2">Add your comment and/or upload images, then click Send.</p>

                <div class="mb-3">
                    <label for="lfComment" class="form-label">Comment</label>
                    <textarea class="form-control" id="lfComment" rows="3" placeholder="Enter your comment..."></textarea>
                </div>

                <div class="mb-3">
                    <label for="lfImages" class="form-label">Upload Images</label>
                    <input type="file" class="form-control" id="lfImages" accept="image/*" multiple>
                    <div id="lfImagePreview" class="d-flex flex-wrap mt-2"></div>
                </div>

                <div id="lfAlert" class="alert d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="lfSendBtn" disabled>
                    <span class="spinner-border spinner-border-sm d-none me-1" id="lfSendSpinner" role="status"></span>
                    Send
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const modalEl = document.getElementById('lostFoundModal');
    const commentEl = document.getElementById('lfComment');
    const imagesEl = document.getElementById('lfImages');
    const sendBtn = document.getElementById('lfSendBtn');
    const sendSpinner = document.getElementById('lfSendSpinner');
    const previewEl = document.getElementById('lfImagePreview');
    const alertEl = document.getElementById('lfAlert');
    let currentReportId = null;

    function showAlert(type, message) {
        alertEl.className = 'alert alert-' + type;
        alertEl.textContent = message;
        alertEl.classList.remove('d-none');
    }

    function hideAlert() {
        alertEl.classList.add('d-none');
    }

    function updateSendButtonState() {
        const hasComment = (commentEl.value || '').trim().length > 0;
        const hasImages = imagesEl.files && imagesEl.files.length > 0;
        sendBtn.disabled = !(hasComment || hasImages);
    }

    function renderImagePreviews() {
        previewEl.innerHTML = '';
        if (!imagesEl.files) return;
        Array.from(imagesEl.files).forEach(function (file) {
            const img = document.createElement('img');
            img.className = 'lf-preview-img';
            img.src = URL.createObjectURL(file);
            img.onload = function () { URL.revokeObjectURL(img.src); };
            previewEl.appendChild(img);
        });
    }

    function resetModalForm() {
        commentEl.value = '';
        imagesEl.value = '';
        previewEl.innerHTML = '';
        hideAlert();
        sendBtn.disabled = true;
        sendSpinner.classList.add('d-none');
    }

    document.querySelectorAll('.lf-view-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            currentReportId = btn.getAttribute('data-id');
            document.getElementById('lfModalTourId').textContent = btn.getAttribute('data-tour-id') || '—';
            document.getElementById('lfModalSubject').textContent = btn.getAttribute('data-subject') || '—';
            document.getElementById('lfModalDescription').textContent = btn.getAttribute('data-description') || '—';
            resetModalForm();
        });
    });

    commentEl.addEventListener('input', updateSendButtonState);
    imagesEl.addEventListener('change', function () {
        renderImagePreviews();
        updateSendButtonState();
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        currentReportId = null;
        resetModalForm();
    });

    sendBtn.addEventListener('click', function () {
        if (!currentReportId || sendBtn.disabled) return;

        const formData = new FormData();
        const comment = (commentEl.value || '').trim();
        if (comment) formData.append('comment', comment);
        Array.from(imagesEl.files || []).forEach(function (file) {
            formData.append('images[]', file);
        });

        sendBtn.disabled = true;
        sendSpinner.classList.remove('d-none');
        hideAlert();

        fetch("{{ route('lost-found.respond', ['id' => '__ID__']) }}".replace('__ID__', currentReportId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
        .then(function (result) {
            if (result.ok && result.data.success) {
                showAlert('success', result.data.message || 'Sent successfully.');
                resetModalForm();
                setTimeout(function () {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }, 1200);
            } else {
                showAlert('danger', result.data.message || 'Failed to send.');
                updateSendButtonState();
            }
        })
        .catch(function () {
            showAlert('danger', 'An error occurred. Please try again.');
            updateSendButtonState();
        })
        .finally(function () {
            sendSpinner.classList.add('d-none');
        });
    });
})();
</script>
@endpush
