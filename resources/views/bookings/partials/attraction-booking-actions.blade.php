{{--
  Booking actions for attraction cards on confirmed / definite / actual.
  Expects: $tour, $attractionOrder, $index, $bookingIndex
--}}
@php
    $roleId = (int) (auth()->user()->role_id ?? 0);
    $canEditApprove = in_array($roleId, [11, 34, 124, 125, 128, 131, 132, 134, 135, 137, 138], true);
    $canReject = in_array($roleId, [11, 34, 33, 37, 38, 124, 125, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138], true);
    $isApproved = (int) ($attractionOrder->is_approve ?? 0) === 1;
    $cancelDateStr = !empty($tour->auto_cancel_date)
        ? \Carbon\Carbon::parse($tour->auto_cancel_date)->format('Y-m-d')
        : '';
@endphp

<div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
    <p class="svc-section-title">Booking Actions</p>
    <div class="svc-actions d-flex flex-wrap gap-1 align-items-center">
        @if($isApproved)
            <span class="svc-status-pill is-approved">
                <i class="ri-check-line"></i> Approved
                @if(!empty($attractionOrder->reference_id))
                    · Ref: {{ $attractionOrder->reference_id }}
                @endif
                @if(!empty($attractionOrder->display_due_date))
                    · Due: {{ $attractionOrder->display_due_date }}
                @endif
            </span>
            <button type="button" class="btn btn-sm svc-btn" style="border:1px solid #0ea5e9;color:#0369a1;"
                    onclick="openAttractionMailPreview({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})">
                <i class="ri-mail-line me-1"></i>Mail Preview
            </button>
        @elseif($canEditApprove || $canReject)
            @if($canEditApprove)
            <button type="button" class="btn btn-sm svc-btn svc-btn-edit"
                    onclick="editIndividualAttraction({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})">
                <i class="ri-pencil-line me-1"></i>Edit
            </button>
            <button type="button" class="btn btn-sm svc-btn svc-btn-approve"
                    onclick="window.approveIndividualAttraction ? window.approveIndividualAttraction({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }}, '{{ $cancelDateStr }}') : approveIndividualAttraction({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }}, '{{ $cancelDateStr }}')">
                <i class="ri-check-line me-1"></i>Approve
            </button>
            @endif
            @if($canReject)
            <button type="button" class="btn btn-sm svc-btn svc-btn-reject"
                    onclick="window.rejectIndividualAttraction ? window.rejectIndividualAttraction({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }}) : rejectIndividualAttraction({{ $tour->tour_id }}, {{ $index }}, {{ $bookingIndex }})">
                <i class="ri-close-line me-1"></i>Reject
            </button>
            @endif
        @else
            <span class="text-muted" style="font-size:0.8rem;">No actions available</span>
        @endif
    </div>
</div>
