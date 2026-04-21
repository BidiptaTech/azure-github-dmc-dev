<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\PackageBooking;
use App\Models\PackageInquiryComment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PackageBookingTemplatesController extends Controller
{
    private function statusColumn(): string
    {
        return Schema::hasColumn('package_bookings', 'booking_status') ? 'booking_status' : 'status';
    }

    private function baseQuery()
    {
        $query = PackageBooking::query()
            ->with(['packageInfo', 'agent', 'bookedBy'])
            ->orderByDesc('created_at');

        $user = Auth::user();
        if (!$user) {
            return $query;
        }

        // If the table supports scoping by DMC, filter to current DMC for non-admin roles.
        if (Schema::hasColumn('package_bookings', 'dmc_id')) {
            $dmcId = CommonHelper::getDmcId($user);
            if (!empty($dmcId) && (int) $dmcId > 0 && (int) ($user->role_id ?? 0) !== 1) {
                $query->where('dmc_id', (int) $dmcId);
            }
        }

        return $query;
    }

    private function listByStatuses(array $statuses, string $view, string $pageTitle)
    {
        $statusColumn = $this->statusColumn();
        $bookings = $this->baseQuery()
            ->whereIn($statusColumn, $statuses)
            ->get();

        $bookingIds = $bookings->pluck('booking_id')->filter()->unique()->values();
        $packageComments = $bookingIds->isEmpty()
            ? collect([])
            : PackageInquiryComment::whereIn('booking_id', $bookingIds->all())
                ->whereNull('deleted_at')
                ->orderByDesc('created_at')
                ->get();

        return view($view, [
            'bookings' => $bookings,
            'statusColumn' => $statusColumn,
            'pageTitle' => $pageTitle,
            'packageComments' => $packageComments,
        ]);
    }

    private function findBookingOrFail(string $bookingId): PackageBooking
    {
        // booking_id is a string like PB00239; also allow numeric id fallback.
        $booking = PackageBooking::query()
            ->where('booking_id', $bookingId)
            ->first();

        if (!$booking && is_numeric($bookingId)) {
            $booking = PackageBooking::query()->where('id', (int) $bookingId)->first();
        }

        abort_if(!$booking, 404, 'Package booking not found');
        return $booking;
    }

    private function setBookingStatus(PackageBooking $booking, string $newStatus, ?string $remark = null, ?float $amount = null, ?float $actualAmount = null): void
    {
        $statusColumn = $this->statusColumn();
        $oldStatus = (string) (data_get($booking, $statusColumn) ?? '');

        $track = [
            'changed_at' => Carbon::now()->toDateTimeString(),
            'changed_by' => Auth::user()?->name,
            'changed_by_user_id' => Auth::user()?->userId ?? Auth::user()?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'remark' => $remark,
            'amount' => $amount,
            'actual_amount' => $actualAmount,
        ];

        $details = is_array($booking->booking_details ?? null) ? $booking->booking_details : (is_string($booking->booking_details ?? null) ? (json_decode($booking->booking_details, true) ?: []) : []);
        $details['status_track'] = array_values(array_merge(($details['status_track'] ?? []), [$track]));
        $booking->booking_details = $details;

        $booking->{$statusColumn} = $newStatus;
        $booking->save();
    }

    public function newEnquiries()
    {
        return $this->listByStatuses(['New Enquiry'], 'package-bookings.new-enquiries', 'Package New Enquiries');
    }

    public function followUps()
    {
        return $this->listByStatuses(['Prospect', 'Tentative'], 'package-bookings.follow-ups', 'Package Follow Ups');
    }

    public function confirmed()
    {
        return $this->listByStatuses(['Confirmed'], 'package-bookings.confirmed', 'Package Confirmed Bookings');
    }

    public function definite()
    {
        return $this->listByStatuses(['Definite'], 'package-bookings.definite', 'Package Definite Bookings');
    }

    public function actual()
    {
        return $this->listByStatuses(['Actual', 'Complete'], 'package-bookings.actual', 'Package Actual Bookings');
    }

    public function cancelled()
    {
        $statusColumn = $this->statusColumn();

        $query = $this->baseQuery();
        if ($statusColumn === 'booking_status') {
            $query->where(function ($q) use ($statusColumn) {
                $q->where($statusColumn, 'Cancelled')
                    ->orWhere($statusColumn, 'like', 'Cancel%');
            });
        } else {
            // Fallback for older schema (status column) - treat "cancelled" as cancelled.
            $query->whereIn($statusColumn, ['cancelled', 'canceled', 'Cancelled', 'Canceled']);
        }

        $bookings = $query->get();

        return view('package-bookings.cancelled', [
            'bookings' => $bookings,
            'statusColumn' => $statusColumn,
            'pageTitle' => 'Package Cancelled Bookings',
        ]);
    }

    public function agentNegotiation(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|string',
            'action' => 'required|in:negotiate,cancel,confirm',
            'amount' => 'nullable|numeric|min:0.01',
            'comment' => 'nullable|string|max:1000',
            'actual_amount' => 'nullable|numeric|min:0',
        ]);

        $booking = $this->findBookingOrFail($validated['booking_id']);
        $statusColumn = $this->statusColumn();
        $currentStatus = (string) (data_get($booking, $statusColumn) ?? '');

        $currentUser = Auth::user();
        $changedByName = $currentUser ? ($currentUser->name ?? '') : null;
        $changedByUserId = $currentUser ? ($currentUser->userId ?? $currentUser->id ?? null) : null;

        $activeEnquiry = PackageInquiryComment::where('booking_id', $booking->booking_id)
            ->where('status', 1)
            ->orderByDesc('created_at')
            ->first();

        if ($validated['action'] === 'negotiate' && empty($validated['amount'])) {
            return back()
                ->withErrors(['amount' => 'Please enter a negotiation amount.'])
                ->withInput();
        }

        if ($validated['action'] === 'negotiate') {
            $actualAmount = (float) ($validated['actual_amount'] ?? 0);
            $amountOffered = (float) ($validated['amount'] ?? 0);

            if ($actualAmount > 0 && $amountOffered > $actualAmount) {
                return back()
                    ->withErrors(['amount' => 'Negotiated amount cannot exceed the current amount.'])
                    ->withInput();
            }

            $lastInquiryId = PackageInquiryComment::withTrashed()->max('package_inquiry_id') ?? 1;
            $newInquiryId = CommonHelper::createId($lastInquiryId);
            while (PackageInquiryComment::withTrashed()->where('package_inquiry_id', $newInquiryId)->exists()) {
                $newInquiryId = CommonHelper::createId($newInquiryId);
            }

            $row = PackageInquiryComment::create([
                'package_inquiry_id' => $newInquiryId,
                'booking_id' => $booking->booking_id,
                'dmc_id' => $booking->dmc_id ?? null,
                'agent_id' => $booking->agent_id ?? null,
                // Match Tours flow: this modal records the agent's offer (sender=agent -> receiver=OM)
                'sender_id' => $booking->agent_id ? (int) $booking->agent_id : null,
                'sender_type' => 'agent',
                'receiver_id' => $changedByUserId ? (int) $changedByUserId : null,
                'receiver_type' => 'OM',
                'current_position' => 'OM',
                'amount' => $amountOffered,
                'actual_amount' => $actualAmount,
                'comment' => (string) ($validated['comment'] ?? ''),
                'status' => 1,
            ]);

            if ($row && $activeEnquiry && $activeEnquiry->id !== $row->id) {
                $activeEnquiry->update(['status' => 0]);
            }

            return back()->with('success', 'Package booking negotiation submitted successfully!');
        }

        if ($validated['action'] === 'cancel') {
            $amountCandidate = !empty($validated['amount']) ? (float) $validated['amount'] : ($activeEnquiry?->amount ? (float) $activeEnquiry->amount : null);
            $actualCandidate = $activeEnquiry?->actual_amount ? (float) $activeEnquiry->actual_amount : (float) ($validated['actual_amount'] ?? 0);
            if (!is_null($amountCandidate) && $actualCandidate > 0 && $amountCandidate > $actualCandidate) {
                return back()
                    ->withErrors(['amount' => 'Amount cannot exceed the current amount.'])
                    ->withInput();
            }
            if ($activeEnquiry) {
                $activeEnquiry->update(['status' => 3]);
            }
            $newStatus = strcasecmp($currentStatus, 'Definite') === 0 ? 'Refund - Pending' : ('Cancel - ' . ($currentStatus ?: 'New Enquiry'));
            $this->setBookingStatus(
                $booking,
                $newStatus,
                (string) ($validated['comment'] ?? null),
                $amountCandidate,
                $actualCandidate
            );
            return back()->with('success', 'Package booking cancelled successfully! Status updated to ' . $newStatus . '.');
        }

        if ($validated['action'] === 'confirm') {
            $amountCandidate = !empty($validated['amount']) ? (float) $validated['amount'] : ($activeEnquiry?->amount ? (float) $activeEnquiry->amount : null);
            $actualCandidate = $activeEnquiry?->actual_amount ? (float) $activeEnquiry->actual_amount : (float) ($validated['actual_amount'] ?? 0);
            if (!is_null($amountCandidate) && $actualCandidate > 0 && $amountCandidate > $actualCandidate) {
                return back()
                    ->withErrors(['amount' => 'Amount cannot exceed the current amount.'])
                    ->withInput();
            }
            if ($activeEnquiry) {
                $activeEnquiry->update(['status' => 2]);
            }
            $this->setBookingStatus(
                $booking,
                'Confirmed',
                (string) ($validated['comment'] ?? null),
                $amountCandidate,
                $actualCandidate
            );
            return back()->with('success', 'Package booking confirmed successfully! Status updated to Confirmed.');
        }

        return back()->with('error', 'Unsupported action requested.');
    }

    public function updateNegotiation(Request $request)
    {
        $validated = $request->validate([
            'package_inquiry_id' => 'required|integer',
            'price' => 'required|numeric|min:0.01',
            'comment' => 'required|string|max:1000',
            'actual_amount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $currentEnquiry = PackageInquiryComment::where('package_inquiry_id', (int) $validated['package_inquiry_id'])->first();
        abort_if(!$currentEnquiry, 404, 'Enquiry not found');

        $booking = $this->findBookingOrFail((string) $currentEnquiry->booking_id);
        $statusColumn = $this->statusColumn();
        $currentStatus = (string) (data_get($booking, $statusColumn) ?? '');

        $currentUser = Auth::user();
        $changedByName = $currentUser ? ($currentUser->name ?? '') : null;
        $changedByUserId = $currentUser ? ($currentUser->userId ?? $currentUser->id ?? null) : null;

        $previousActive = PackageInquiryComment::where('booking_id', $booking->booking_id)
            ->where('status', 1)
            ->orderByDesc('created_at')
            ->first();

        // Create a new enquiry record (keep full negotiation history, like enquiry_comments)
        $lastInquiryId = PackageInquiryComment::withTrashed()->max('package_inquiry_id') ?? 1;
        $newInquiryId = CommonHelper::createId($lastInquiryId);
        while (PackageInquiryComment::withTrashed()->where('package_inquiry_id', $newInquiryId)->exists()) {
            $newInquiryId = CommonHelper::createId($newInquiryId);
        }

        $newNegotiated = (float) $validated['price'];

        $row = PackageInquiryComment::create([
            'package_inquiry_id' => $newInquiryId,
            'booking_id' => $booking->booking_id,
            'dmc_id' => $booking->dmc_id ?? null,
            'agent_id' => $booking->agent_id ?? null,
            'sender_id' => $changedByUserId ? (int) $changedByUserId : null,
            'sender_type' => 'OM',
            'receiver_id' => $booking->agent_id ? (int) $booking->agent_id : null,
            'receiver_type' => 'agent',
            'current_position' => 'agent',
            'amount' => $newNegotiated,
            // Store DMC counter-offer as the effective "current" amount for the next agent turn.
            'actual_amount' => $newNegotiated,
            'comment' => (string) $validated['comment'],
            'status' => 1,
        ]);

        // Mark previous active enquiry inactive (if different)
        if ($previousActive && $row && $previousActive->id !== $row->id) {
            $previousActive->update(['status' => 0]);
        }

        // Status transitions like Tours: New Enquiry -> Prospect -> Tentative
        if ($currentStatus === 'New Enquiry') {
            $this->setBookingStatus($booking, 'Prospect', (string) $validated['comment'], null, $newNegotiated);
        } elseif ($currentStatus === 'Prospect') {
            $this->setBookingStatus($booking, 'Tentative', (string) $validated['comment'], null, $newNegotiated);
        } else {
            // no status change, still append to track via setBookingStatus to same status
            $this->setBookingStatus($booking, $currentStatus ?: 'New Enquiry', (string) $validated['comment'], null, $newNegotiated);
        }

        return back()->with('success', 'Price updated successfully!');
    }

    public function cancelBooking(Request $request, string $bookingId)
    {
        $booking = $this->findBookingOrFail($bookingId);
        $statusColumn = $this->statusColumn();
        $oldStatus = (string) (data_get($booking, $statusColumn) ?? '');

        $newStatus = strcasecmp($oldStatus, 'Definite') === 0 ? 'Refund - Pending' : ('Cancel - ' . ($oldStatus ?: 'New Enquiry'));
        $this->setBookingStatus($booking, $newStatus);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'booking_id' => $booking->booking_id,
            'new_status' => $newStatus,
        ]);
    }
}

