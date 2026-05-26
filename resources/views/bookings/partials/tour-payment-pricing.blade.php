{{-- Requires $tour and $tourTotalPrice. Sets: grossTourAmount, discountAmount, priceAfterFoc, baseAmount, netTourAmount, negotiationDiscount --}}
<?php
    $confirmedEnquiry = \App\Models\Enquiry::where('tour_id', $tour->tour_id)
        ->where('status', 2)
        ->orderByDesc('enquiry_id')
        ->first();
    $latestEnquiryRow = \App\Models\Enquiry::where('tour_id', $tour->tour_id)
        ->orderByDesc('enquiry_id')
        ->first();
    $lastNegotiatedAmount = 0;
    if ($confirmedEnquiry && (float) ($confirmedEnquiry->amount ?? 0) > 0) {
        $lastNegotiatedAmount = (float) $confirmedEnquiry->amount;
    } elseif ($latestEnquiryRow && (float) ($latestEnquiryRow->amount ?? 0) > 0) {
        $lastNegotiatedAmount = (float) $latestEnquiryRow->amount;
    }
    $grossTourAmount = round($tourTotalPrice);
    $tourFocDiscount = max(0, (float) ($tour->getAttributes()['discount_amount'] ?? $tour->discount_amount ?? 0));
    $discountAmount = $tourFocDiscount;
    $priceAfterFoc = max(0, $grossTourAmount - $discountAmount);
    $baseAmount = $lastNegotiatedAmount > 0 ? $lastNegotiatedAmount : $priceAfterFoc;
    $netTourAmount = $baseAmount;
    $negotiationDiscount = max(0, $priceAfterFoc - $baseAmount);
?>
