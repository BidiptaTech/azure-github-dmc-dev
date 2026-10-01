{{-- Payment modal: actual price, confirmed negotiation, last markup from tours.currency_markups. --}}
@php
    $payCur = strtoupper(trim((string) ($tourCurrency ?? 'SGD')));
    $payFmt = static fn ($n) => number_format((float) $n, 2);
    $payTrim = static fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    $payTypeLabel = static function ($type, $raw) use ($payTrim) {
        $type = strtolower(trim((string) $type));
        $raw = (float) $raw;
        if ($type === 'percentage') {
            return $payTrim($raw) . '%';
        }
        if ($type === 'foc') {
            return 'FOC';
        }
        if ($type === 'flat' || $type === 'fixed') {
            return $raw > 0 ? ('Fixed ' . $payTrim($raw)) : 'Fixed';
        }
        return $raw > 0 ? $payTrim($raw) : '—';
    };

    $grossAmt = (float) ($grossTourAmount ?? 0);
    $markupMoney = (float) ($tourMarkupMoney ?? 0);
    $discountMoney = (float) ($tourDiscountMoney ?? ($discountAmount ?? 0));
    $actualPrice = (float) ($netPayableBase ?? max(0, $grossAmt + $markupMoney - $discountMoney));
    $confirmedPrice = (float) ($lastNegotiatedAmount ?? 0);
    $hasConfirmed = $confirmedPrice > 0;
    $invoiceBase = (float) ($baseAmount ?? ($hasConfirmed ? $confirmedPrice : $actualPrice));
    $taxAmt = (float) ($taxAmount ?? 0);
    $invoiceTotal = (float) ($finalAmount ?? ($invoiceBase + $taxAmt));
    $paidAmt = (float) ($totalPaid ?? 0);
    $remainAmt = (float) ($remainingAmount ?? max(0, $invoiceTotal - $paidAmt));

    $markupType = strtolower((string) ($tourMarkupType ?? ($tour->markup_type ?? '')));
    $discountType = strtolower((string) ($tourDiscountType ?? ($tour->discount_type ?? '')));
    $markupRaw = (float) ($tourMarkupRaw ?? ($tour->getAttributes()['markup_amount'] ?? $tour->markup_amount ?? 0));
    $discountRaw = (float) ($tourDiscountRaw ?? ($tour->getAttributes()['discount_amount'] ?? $tour->discount_amount ?? 0));

    $hotelMarkupRaw = $markupRaw;
    $otherMarkupRaw = 0.0;
    $hotelMarkupMoney = $markupMoney;
    $otherMarkupMoney = 0.0;

    $cmRaw = $tour->currency_markups ?? ($tour->getAttributes()['currency_markups'] ?? null);
    if (is_string($cmRaw)) {
        $decodedCm = json_decode($cmRaw, true);
        $cmRaw = (json_last_error() === JSON_ERROR_NONE) ? $decodedCm : null;
    }
    if (is_string($cmRaw)) {
        $decodedCm = json_decode($cmRaw, true);
        $cmRaw = (json_last_error() === JSON_ERROR_NONE) ? $decodedCm : null;
    }

    $markupRows = [];
    if (is_array($cmRaw)) {
        $hotelSumFlat = 0.0;
        $otherSumFlat = 0.0;
        $discSumFlat = 0.0;
        foreach ($cmRaw as $key => $row) {
            if (!is_array($row)) {
                continue;
            }
            $city = trim((string) ($row['city'] ?? ''));
            if (str_starts_with($city, '__lite_markup_pad__')) {
                continue;
            }
            $country = trim((string) ($row['country'] ?? ''));
            $rowCur = strtoupper(trim((string) ($row['currency'] ?? '')));
            if ($rowCur === '' && is_string($key) && !is_numeric($key)) {
                $rowCur = strtoupper(trim($key));
            }
            $mt = strtolower(trim((string) ($row['markup_type'] ?? '')));
            if ($mt === 'fixed') {
                $mt = 'flat';
            }
            $dt = strtolower(trim((string) ($row['discount_type'] ?? '')));
            if ($dt === 'fixed') {
                $dt = 'flat';
            }
            $hRaw = (float) ($row['hotel_markup'] ?? $row['markup_value'] ?? 0);
            $oRaw = (float) ($row['other_markup'] ?? 0);
            $dRaw = (float) ($row['discount_value'] ?? 0);
            if ($city === '' && $country === '' && $rowCur === '' && $hRaw <= 0 && $oRaw <= 0 && $dRaw <= 0) {
                continue;
            }
            $place = $city !== '' ? $city : ($country !== '' ? $country : ($rowCur !== '' ? $rowCur : 'Tour'));
            $markupRows[] = [
                'place' => $place,
                'currency' => $rowCur,
                'markup_type' => $mt,
                'hotel_raw' => $hRaw,
                'other_raw' => $oRaw,
                'discount_type' => $dt,
                'discount_raw' => $dRaw,
            ];
            if ($mt === 'flat') {
                $hotelSumFlat += $hRaw;
                $otherSumFlat += $oRaw;
            }
            if ($dt === 'flat' || $dt === 'foc') {
                $discSumFlat += $dRaw;
            }
        }
        if ($markupRows !== []) {
            $preferred = $markupRows[0];
            foreach ($markupRows as $row) {
                if ($payCur !== '' && strtoupper((string) $row['currency']) === $payCur) {
                    $preferred = $row;
                    break;
                }
            }
            $hotelMarkupRaw = (float) $preferred['hotel_raw'];
            $otherMarkupRaw = (float) $preferred['other_raw'];
            $markupType = $preferred['markup_type'] !== '' ? $preferred['markup_type'] : $markupType;
            $discountType = $preferred['discount_type'] !== '' ? $preferred['discount_type'] : $discountType;
            $discountRaw = (float) $preferred['discount_raw'];
            if ($markupType === 'flat') {
                $hotelMarkupMoney = $hotelSumFlat;
                $otherMarkupMoney = $otherSumFlat;
                $markupMoney = $hotelMarkupMoney + $otherMarkupMoney;
            }
            if (($discountType === 'flat' || $discountType === 'foc') && $discSumFlat > 0) {
                $discountMoney = $discSumFlat;
            }
        }
    }

    $hotelTypeText = $payTypeLabel($markupType, $hotelMarkupRaw);
    $otherTypeText = $payTypeLabel($markupType, $otherMarkupRaw);
    $discTypeText = $payTypeLabel($discountType, $discountRaw);
@endphp

@once
<style>
    .add-pay-breakdown { font-size: 0.78rem; line-height: 1.25; }
    .add-pay-breakdown .apb-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 6px;
        margin-bottom: 6px;
    }
    .add-pay-breakdown .apb-grid.apb-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .add-pay-breakdown .apb-item {
        background: #fff;
        border: 1px solid #dbe3ee;
        border-radius: 6px;
        padding: 5px 8px;
        min-width: 0;
    }
    .add-pay-breakdown .apb-item.is-accent { border-color: #93c5fd; background: #eff6ff; }
    .add-pay-breakdown .apb-item.is-ok { border-color: #86efac; background: #f0fdf4; }
    .add-pay-breakdown .apb-lbl {
        display: block;
        font-size: 0.62rem;
        letter-spacing: .03em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 1px;
    }
    .add-pay-breakdown .apb-val { font-weight: 700; font-size: 0.82rem; white-space: nowrap; }
    .add-pay-breakdown .apb-sub { font-size: 0.68rem; color: #64748b; }
    .add-pay-breakdown table.apb-table {
        font-size: 0.72rem;
        margin: 0 0 6px;
        background: #fff;
    }
    .add-pay-breakdown table.apb-table th,
    .add-pay-breakdown table.apb-table td {
        padding: 3px 6px;
        vertical-align: middle;
    }
    .add-pay-breakdown table.apb-table th {
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #64748b;
        font-weight: 700;
        background: #f8fafc;
        white-space: nowrap;
        border-bottom-width: 1px;
    }
    .add-pay-breakdown .apb-invoice {
        display: flex;
        flex-wrap: wrap;
        gap: 4px 14px;
        justify-content: space-between;
        margin-top: 6px;
        background: #fff;
        border: 1px solid #dbe3ee;
        border-radius: 6px;
        padding: 6px 8px;
    }
    .add-pay-breakdown .apb-invoice > div { min-width: 0; }
    @media (max-width: 575.98px) {
        .add-pay-breakdown .apb-grid,
        .add-pay-breakdown .apb-grid.apb-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>
@endonce

<div class="add-pay-breakdown">
    <div class="apb-grid">
        <div class="apb-item">
            <span class="apb-lbl">Gross</span>
            <div class="apb-val text-secondary">{{ $payFmt(ceil($grossAmt)) }} {{ $payCur }}</div>
        </div>
        <div class="apb-item">
            <span class="apb-lbl">Hotel markup</span>
            <div class="apb-val text-info">{{ $hotelTypeText }}</div>
            @if($markupType === 'flat')
                <div class="apb-sub">+ {{ $payFmt($hotelMarkupMoney) }} {{ $payCur }}</div>
            @endif
        </div>
        <div class="apb-item">
            <span class="apb-lbl">Other markup</span>
            <div class="apb-val text-info">{{ $otherTypeText }}</div>
            @if($markupType === 'flat')
                <div class="apb-sub">+ {{ $payFmt($otherMarkupMoney) }} {{ $payCur }}</div>
            @endif
        </div>
        <div class="apb-item">
            <span class="apb-lbl">Discount</span>
            <div class="apb-val text-success">− {{ $payFmt($discountMoney) }} {{ $payCur }}</div>
            <div class="apb-sub">{{ $discTypeText }}</div>
        </div>
    </div>

    <div class="apb-grid apb-grid-2">
        <div class="apb-item is-accent">
            <span class="apb-lbl">Actual price</span>
            <div class="apb-val text-primary">{{ $payFmt(ceil($actualPrice)) }} {{ $payCur }}</div>
            <div class="apb-sub">Gross + markup − discount</div>
        </div>
        <div class="apb-item {{ $hasConfirmed ? 'is-ok' : '' }}">
            <span class="apb-lbl">Confirmed price</span>
            @if($hasConfirmed)
                <div class="apb-val text-success">{{ $payFmt(ceil($confirmedPrice)) }} {{ $payCur }}</div>
                <div class="apb-sub">
                    After negotiation
                    @if(abs($confirmedPrice - $actualPrice) > 0.009)
                        · {{ $confirmedPrice < $actualPrice ? '−' : '+' }}{{ $payFmt(abs($confirmedPrice - $actualPrice)) }} vs actual
                    @endif
                </div>
            @else
                <div class="apb-val text-muted">Same as actual</div>
                <div class="apb-sub">No separate confirmed amount</div>
            @endif
        </div>
    </div>

    @if(!empty($markupRows))
        <div class="table-responsive">
            <table class="table table-sm table-bordered apb-table mb-0">
                <thead>
                    <tr>
                        <th>City / country</th>
                        <th>Hotel</th>
                        <th>Other</th>
                        <th>Discount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($markupRows as $row)
                        <tr>
                            <td>
                                {{ $row['place'] }}
                                @if(!empty($row['currency']))
                                    <span class="text-muted">({{ $row['currency'] }})</span>
                                @endif
                            </td>
                            <td>{{ $payTypeLabel($row['markup_type'], $row['hotel_raw']) }}</td>
                            <td>{{ $payTypeLabel($row['markup_type'], $row['other_raw']) }}</td>
                            <td>{{ $payTypeLabel($row['discount_type'], $row['discount_raw']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="apb-sub mb-1">No city markup rows saved. Showing tour-level rates only.</div>
    @endif

    <div class="apb-invoice">
        <div>
            <span class="apb-lbl">Invoice base</span>
            <div class="apb-val">{{ $payFmt(round($invoiceBase)) }} {{ $payCur }}</div>
        </div>
        <div>
            <span class="apb-lbl">Tax</span>
            <div class="apb-val text-warning">{{ $payFmt(round($taxAmt)) }} {{ $payCur }}</div>
        </div>
        <div>
            <span class="apb-lbl">Total</span>
            <div class="apb-val text-primary">{{ $payFmt(round($invoiceTotal)) }} {{ $payCur }}</div>
        </div>
        <div>
            <span class="apb-lbl">Paid</span>
            <div class="apb-val text-success">{{ $payFmt(round($paidAmt)) }} {{ $payCur }}</div>
        </div>
        <div>
            <span class="apb-lbl">Remaining</span>
            <div class="apb-val text-danger">{{ $payFmt(round($remainAmt)) }} {{ $payCur }}</div>
        </div>
    </div>
</div>
