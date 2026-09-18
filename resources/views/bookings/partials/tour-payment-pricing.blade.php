{{-- Requires $tour and $tourTotalPrice. Sets: grossTourAmount, tourMarkupType, tourMarkupMoney, tourDiscountType, tourDiscountMoney, discountAmount, priceAfterFoc, baseAmount, netTourAmount, negotiationDiscount --}}
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

    // Markup stored on the tour (increases payable amount).
    $tourMarkupType = $tour->markup_type ?? null;
    $tourMarkupRaw = (float) ($tour->getAttributes()['markup_amount'] ?? $tour->markup_amount ?? 0);
    $tourMarkupOn = ((int) ($tour->markup ?? 0) === 1)
        && $tourMarkupRaw > 0
        && in_array($tourMarkupType, ['percentage', 'flat'], true);
    $tourMarkupMoney = $tourMarkupOn
        ? ($tourMarkupType === 'percentage'
            ? ($grossTourAmount * $tourMarkupRaw / 100)
            : $tourMarkupRaw)
        : 0;
    $tourMarkupMoney = max(0, $tourMarkupMoney);

    // Discount stored on the tour, applied after markup.
    $tourDiscountType = $tour->discount_type ?? null;
    $tourDiscountRaw = (float) ($tour->getAttributes()['discount_amount'] ?? $tour->discount_amount ?? 0);
    $discountBaseAmount = $grossTourAmount + $tourMarkupMoney;
    if ($tourDiscountType === 'percentage') {
        $tourDiscountMoney = $discountBaseAmount * $tourDiscountRaw / 100;
    } else {
        $tourDiscountMoney = $tourDiscountRaw;
    }
    $tourDiscountMoney = max(0, $tourDiscountMoney);

    $discountAmount = $tourDiscountMoney;
    $priceAfterFoc = max(0, $grossTourAmount + $tourMarkupMoney - $tourDiscountMoney);
    $netPayableBase = (int) ceil($priceAfterFoc);
    $baseAmount = $lastNegotiatedAmount > 0 ? $lastNegotiatedAmount : $netPayableBase;
    $netTourAmount = $baseAmount;
    $negotiationDiscount = max(0, $netPayableBase - $baseAmount);
?>
