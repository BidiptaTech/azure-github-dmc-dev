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

    // Markup rates are display-only — sell/gross already includes hotel/other markup.
    $tourMarkupType = $tour->markup_type ?? null;
    $tourMarkupRaw = (float) ($tour->getAttributes()['markup_amount'] ?? $tour->markup_amount ?? 0);
    $tourMarkupMoney = 0;

    // Prefer currency_markups discount (agency/tour) when present; else tour columns.
    $tourDiscountType = $tour->discount_type ?? null;
    $tourDiscountRaw = (float) ($tour->getAttributes()['discount_amount'] ?? $tour->discount_amount ?? 0);
    $cmForPay = $tour->currency_markups ?? ($tour->getAttributes()['currency_markups'] ?? null);
    if (is_string($cmForPay)) {
        $decodedCmPay = json_decode($cmForPay, true);
        $cmForPay = (json_last_error() === JSON_ERROR_NONE) ? $decodedCmPay : null;
    }
    if (is_array($cmForPay)) {
        foreach ($cmForPay as $cmRow) {
            if (! is_array($cmRow) || str_starts_with((string) ($cmRow['city'] ?? ''), '__lite_markup_pad__')) {
                continue;
            }
            $dRawPay = (float) ($cmRow['discount_value'] ?? 0);
            $dTypePay = strtolower(trim((string) ($cmRow['discount_type'] ?? '')));
            if ($dTypePay === 'fixed') {
                $dTypePay = 'flat';
            }
            if ($dRawPay > 0 && in_array($dTypePay, ['percentage', 'flat', 'foc'], true)) {
                $tourDiscountType = $dTypePay;
                $tourDiscountRaw = $dRawPay;
                break;
            }
        }
    }
    // Discount on gross only (markup already baked into sell).
    if ($tourDiscountType === 'percentage') {
        $tourDiscountMoney = $grossTourAmount * $tourDiscountRaw / 100;
    } else {
        $tourDiscountMoney = $tourDiscountRaw;
    }
    $tourDiscountMoney = max(0, $tourDiscountMoney);

    $discountAmount = $tourDiscountMoney;
    $priceAfterFoc = max(0, $grossTourAmount - $tourDiscountMoney);
    $netPayableBase = (int) ceil($priceAfterFoc);
    $baseAmount = $lastNegotiatedAmount > 0 ? $lastNegotiatedAmount : $netPayableBase;
    $netTourAmount = $baseAmount;
    $negotiationDiscount = max(0, $netPayableBase - $baseAmount);
?>
