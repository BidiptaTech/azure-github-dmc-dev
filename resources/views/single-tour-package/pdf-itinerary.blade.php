<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Quotation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #212529;
            padding: 20px;
            background: #ffffff;
        }
        /* Quotation Page Styles */
        .quotation-page {
            page-break-after: always;
            background: #ffffff;
            padding: 20px;
            margin: 0;
            width: 100%;
        }
        /* Content wrapper for pages after quotation */
        .content-wrapper {
            padding: 0;
            background: #ffffff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: auto;
        }
        thead {
            display: table-header-group;
        }
        tbody {
            display: table-row-group;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th, td {
            padding: 6px;
            text-align: left;
            border: 1px solid #e0e0e0;
        }
        th {
            background-color: #f0f8ff;
            font-weight: bold;
            color: #2c3e50;
            page-break-after: avoid;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .invoice-info {
            margin-bottom: 15px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #2c3e50;
            padding: 8px 0;
            border-bottom: 2px solid #34495e;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .mb-2 {
            margin-bottom: 8px;
        }
        .mt-2 {
            margin-top: 8px;
        }
        .header-top {
            text-align: center;
            margin-bottom: 16px;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .dmc-logo-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        .dmc-logo {
            max-width: 70px;
            max-height: 70px;
            object-fit: contain;
        }
        .quotation-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dee2e6;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .quotation-table td {
            border: 1px solid #e0e0e0;
            padding: 4px 6px;
            vertical-align: top;
            margin: 0;
        }
        .quotation-table tr {
            margin: 0;
        }
        .quotation-label {
            background: #f0f8ff;
            font-weight: bold;
            padding: 4px 6px;
            border: 1px solid #e0e0e0;
            color: #2c3e50;
        }
        .quotation-value {
            background: #ffffff;
            padding: 4px 6px;
            border: 1px solid #e0e0e0;
            color: #212529;
        }
        /* Hotel Options Excel-like Styles */
        .hotel-options-section {
            margin: 0 0 20px 0;
            padding: 0;
            width: 100%;
        }
        .hotel-option-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dee2e6;
            font-size: 11px;
            margin: 0 0 20px 0;
            padding: 0;
            page-break-inside: auto;
        }
        .hotel-option-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .hotel-option-table td {
            border: 1px solid #e0e0e0;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .hotel-option-header {
            background: #a0aec0;
            color: #2c3e50;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            padding: 6px;
        }
        .hotel-option-label {
            background: #f0f8ff;
            font-weight: bold;
            padding: 4px 6px;
            color: #2c3e50;
        }
        .hotel-option-value {
            background: #ffffff;
            padding: 4px 6px;
            color: #212529;
        }
        .hotel-total-row {
            background: #7f8c8d;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 6px;
        }
        .hotel-supplemental-header {
            background: #e8f5e9;
            color: #2c3e50;
            font-weight: bold;
            text-align: center;
            padding: 6px;
            border: 1px solid #c8e6c9;
        }
        .hotel-supplemental-cell {
            background: #e8f5e9;
            color: #2c3e50;
            font-weight: bold;
            text-align: center;
            padding: 4px 6px;
            border: 1px solid #c8e6c9;
        }
        .header-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px 24px;
            border: 1px solid #e5e7eb;
            margin-bottom: 16px;
            box-shadow: 0 8px 18px rgba(15,23,42,0.04);
        }
        .header-top {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .dmc-logo {
            max-width: 120px;
            max-height: 60px;
            object-fit: contain;
        }
        .dmc-company-name {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            flex: 1;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e0ebff;
            color: #1d4ed8;
            font-weight: 600;
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .header-card h1 {
            margin: 10px 0 4px;
            font-size: 26px;
            color: #0f172a;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        .meta-tile {
            padding: 10px 14px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-tile span {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #94a3b8;
            margin-bottom: 3px;
        }
        .meta-tile strong {
            font-size: 14px;
            color: #0f172a;
            font-weight: 600;
        }
        .section {
            margin-bottom: 16px;
            page-break-inside: auto;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .section-header h3 {
            margin: 0;
            font-size: 17px;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 700;
            padding: 8px 12px;
            background: linear-gradient(135deg, #e0ebff 0%, #c7d2fe 100%);
            border-radius: 8px;
            display: inline-block;
            border: 2px solid #3b82f6;
        }
        .section-header span {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 12px;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .service-card {
            background: #fff;
            border-radius: 12px;
            padding: 14px;
            border: 2px solid #e2e8f0;
            display: flex;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(15,23,42,0.08);
            page-break-inside: avoid;
            break-inside: avoid;
            transition: all 0.2s;
        }
        .service-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
        }
        .service-body {
            flex: 1;
            min-width: 0;
        }
        .service-title {
            font-size: 16px;
            color: #0f172a;
            font-weight: 700;
            margin: 0 0 3px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            letter-spacing: -0.01em;
        }
        .service-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-weight: 500;
        }
        .chip-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 5px;
            margin-bottom: 8px;
        }
        .chip {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 11px;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            font-weight: 500;
        }
        .notes {
            margin-top: 6px;
            font-size: 11px;
            color: #475569;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .rooms-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #fbbf24;
            box-shadow: 0 2px 4px rgba(251, 191, 36, 0.15);
        }
        .vehicle-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 2px solid #10b981;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.15);
        }
        .vehicle-line {
            font-size: 14px;
            color: #064e3b;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .vehicle-meta {
            font-size: 11px;
            color: #065f46;
            margin: 0 0 5px;
            font-weight: 500;
        }
        .vehicle-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .vehicle-chip {
            padding: 4px 9px;
            border-radius: 999px;
            background: #ffffff;
            font-size: 11px;
            color: #065f46;
            border: 1px solid #10b981;
            font-weight: 600;
        }
        .room-line {
            font-size: 14px;
            color: #78350f;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }
        .bed-list {
            margin: 0;
            padding-left: 16px;
            color: #92400e;
            font-size: 11px;
            line-height: 1.6;
        }
        .bed-list li {
            margin-bottom: 4px;
            font-weight: 500;
        }
        .hotel-info-block {
            margin-top: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 2px solid #3b82f6;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.2);
        }
        .hotel-info-line {
            font-size: 15px;
            color: #1e40af;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.01em;
        }
        .hotel-info-meta {
            font-size: 12px;
            color: #1e3a8a;
            margin: 4px 0;
            font-weight: 500;
        }
        .hotel-time-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }
        .hotel-time-chip {
            padding: 5px 10px;
            border-radius: 6px;
            background: #ffffff;
            font-size: 11px;
            color: #1e40af;
            border: 2px solid #3b82f6;
            font-weight: 600;
        }
        .detail-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            border: 2px solid #0ea5e9;
            box-shadow: 0 2px 4px rgba(14, 165, 233, 0.15);
        }
        .detail-line {
            font-size: 14px;
            color: #0c4a6e;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .detail-meta {
            font-size: 11px;
            color: #075985;
            margin: 0 0 4px;
        }
        .detail-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .detail-chip {
            padding: 4px 9px;
            border-radius: 999px;
            background: #ffffff;
            font-size: 11px;
            color: #0c4a6e;
            border: 2px solid #0ea5e9;
            font-weight: 600;
        }
        .guide-block {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            box-shadow: 0 2px 4px rgba(245, 158, 11, 0.15);
        }
        .guide-line {
            font-size: 14px;
            color: #78350f;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }
        .guide-meta {
            font-size: 11px;
            color: #92400e;
            margin: 0 0 5px;
            font-weight: 500;
        }
        .guide-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .guide-chip {
            padding: 4px 9px;
            border-radius: 999px;
            background: #ffffff;
            font-size: 11px;
            color: #92400e;
            border: 2px solid #f59e0b;
            font-weight: 600;
        }
        .empty-state {
            background: #fff;
            border-radius: 14px;
            padding: 28px;
            text-align: center;
            border: 1px dashed #c7d2fe;
            color: #64748b;
            font-size: 12px;
        }
        .price-summary-section {
            margin-top: 32px;
            page-break-inside: avoid;
        }
        .price-summary-header {
            background: #6c7a89;
            color: #ffffff;
            padding: 18px 20px;
            border-radius: 12px 12px 0 0;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .08em;
            border: 1px solid #5a6c7d;
            border-bottom: none;
        }
        .price-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 12px 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.1);
        }
        .price-summary-item {
            padding: 24px 16px;
            text-align: center;
            border-right: 1px solid #e0e0e0;
            background: #f0f8ff;
        }
        .price-summary-item:last-child {
            border-right: none;
        }
        .price-summary-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #2c3e50;
            margin-bottom: 12px;
            font-weight: 700;
        }
        .price-summary-value {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1.2;
        }
        .price-summary-currency {
            font-size: 20px;
            vertical-align: top;
            margin-right: 3px;
            color: #2c3e50;
            font-weight: 700;
        }
    </style>
</head>
<body>
    @php
        $checkIn = $tour->check_in_time ? \Carbon\Carbon::parse($tour->check_in_time)->format('d M Y') : '-';
        $checkOut = $tour->check_out_time ? \Carbon\Carbon::parse($tour->check_out_time)->format('d M Y') : '-';
        $totalServices = collect($servicesByType ?? [])->flatten(1)->count();
    @endphp

    <!-- Quotation First Page (Proforma Style) -->
    <div class="quotation-page">
        <!-- Header -->
        <div class="header">
            @php
                $dmcLogoSrc = null;
                if (!empty($dmcLogo) && strpos($dmcLogo, 'data:image') === 0) {
                    $dmcLogoSrc = $dmcLogo;
                } elseif (!empty($dmcLogo)) {
                    try {
                        if (preg_match('/^https?:\/\//i', $dmcLogo)) {
                            $logoContent = @file_get_contents($dmcLogo);
                        } else {
                            $logoPath = public_path(ltrim($dmcLogo, '/'));
                            $logoContent = @file_get_contents($logoPath);
                        }
                        if ($logoContent) {
                            $base64 = base64_encode($logoContent);
                            $dmcLogoSrc = 'data:image/png;base64,' . $base64;
                        }
                    } catch (\Exception $e) {
                        $dmcLogoSrc = null;
                    }
                }
            @endphp
            @if($dmcLogoSrc)
            <div class="header-top">
                <div class="dmc-logo-wrapper">
                    <img src="{{ $dmcLogoSrc }}" class="dmc-logo" />
                </div>
            </div>
                @endif
            <h1>TOUR QUOTATION</h1>
            <p><strong>{{ $dmcDetails['company_name'] ?? $dmcCompanyName ?? 'DMC Name' }}</strong></p>
            <p>Tour ID: <strong>{{ $tour->display_id ?? ('Tour #' . ($tour->tour_id ?? '-')) }}</strong></p>
            </div>

        <!-- Client/Guest Information -->
        <table class="invoice-info">
            <tr>
                <td colspan="4" style="background-color: #f0f8ff; font-weight: bold; padding: 6px; border: 1px solid #e0e0e0;">Client/Guest Information:</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; width: 25%;">Lead Guest:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; width: 25%;">{{ $bookingDetails['lead_guest_name'] ?? 'N/A' }}</td>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; width: 25%;">Address:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; width: 25%;">{{ $bookingDetails['address'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">State:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $bookingDetails['city'] ?? 'N/A' }}</td>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Postal Code:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $bookingDetails['postal_code'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Email:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $bookingDetails['email'] ?? 'N/A' }}</td>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Phone:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $bookingDetails['phone'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Booking ID:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $bookingDetails['booking_id'] ?? 'N/A' }}</td>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">No. of Adults:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $bookingDetails['no_of_adults'] ?? 0 }}</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">No. of Children:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $bookingDetails['no_of_children'] ?? 0 }}</td>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">No. of Infants:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $bookingDetails['no_of_infants'] ?? 0 }}</td>
            </tr>
        </table>

        <!-- Proposal Details -->
        <table class="invoice-info" style="width: 50%; float: right;">
            <tr>
                <td colspan="2" style="background-color: #f0f8ff; font-weight: bold; padding: 6px; border: 1px solid #e0e0e0;">Proposal Details:</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; width: 40%;">Postal / Pin:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; width: 60%;">{{ $dmcDetails['postal_pin'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Proposal Date:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $proposalDetails['proposal_date'] ?? ($generatedAt->format('jS M Y') ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Proposal Validity:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $proposalDetails['proposal_validity'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Proposal Sent by:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $proposalDetails['proposal_sent_by'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;"></td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;"></td>
            </tr>
            <tr>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;"></td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;"></td>
            </tr>
        </table>

        <div style="clear: both;"></div>

        <!-- Travel Company/Agent Information -->
        @if(!empty($agentDetails))
        <table class="invoice-info">
            <tr>
                <td colspan="2" style="background-color: #f0f8ff; font-weight: bold; padding: 6px; border: 1px solid #e0e0e0;">Travel Company / Agent Name: {{ $agentDetails['name'] ?? 'N/A' }}</td>
            </tr>
            @if(!empty($agentDetails['company_name']))
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; width: 40%;">Travel Agency:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; width: 60%;">{{ $agentDetails['company_name'] ?? 'N/A' }}</td>
            </tr>
        @endif
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Address:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $agentDetails['address'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Contact Person:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $agentDetails['contact_person'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Phone:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $agentDetails['phone'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Email:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $agentDetails['email'] ?? 'N/A' }}</td>
            </tr>
        </table>
        @endif

        <!-- Travel Dates & Destination -->
        <table class="invoice-info">
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; width: 20%;">Destination:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; width: 30%;">{{ $travelDetails['destination'] ?? ($tour->destination ?? 'N/A') }}</td>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; width: 20%;">Duration / No of Days:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; width: 30%;">{{ $travelDetails['duration'] ?? ($tourDuration ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Travel Date From:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $travelDetails['travel_date_from'] ?? ($checkIn ?? 'N/A') }}</td>
                <td style="background-color: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0;">Travel Date To:</td>
                <td style="background-color: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;">{{ $travelDetails['travel_date_to'] ?? ($checkOut ?? 'N/A') }}</td>
            </tr>
        </table>
            </div>

    <!-- Existing Content (Itinerary Details) -->
    <div class="content-wrapper">
    <!-- Header -->
    <div class="header">
        @php
            $dmcLogoSrc = null;
            if (!empty($dmcLogo) && strpos($dmcLogo, 'data:image') === 0) {
                $dmcLogoSrc = $dmcLogo;
            } elseif (!empty($dmcLogo)) {
                try {
                    if (preg_match('/^https?:\/\//i', $dmcLogo)) {
                        $logoContent = @file_get_contents($dmcLogo);
                    } else {
                        $logoPath = public_path(ltrim($dmcLogo, '/'));
                        $logoContent = @file_get_contents($logoPath);
                    }
                    if ($logoContent) {
                        $base64 = base64_encode($logoContent);
                        $dmcLogoSrc = 'data:image/png;base64,' . $base64;
                    }
                } catch (\Exception $e) {
                    $dmcLogoSrc = null;
                }
            }
        @endphp
        
        
            </div>

    

    <!-- Hotel Options Section (Excel-like format) -->
    @if(!empty($hotelOptions) && count($hotelOptions) > 0)
        <div class="hotel-options-section">
            @foreach($hotelOptions as $hotel)
                <table class="hotel-option-table" style="width: 100%; border-collapse: collapse; border: 2px solid #000000; font-size: 11px; margin: 0 0 20px 0; padding: 0;">
                    <!-- OPTION Header -->
                    <tr>
                        <td colspan="5" class="hotel-option-header" style="background: #a0aec0; color: #2c3e50; text-align: center; font-weight: bold; font-size: 12px; padding: 6px; border: 1px solid #90a0b0;">
                            ACCOMMODATION {{ $hotel['option_number'] }}
                        </td>
                    </tr>
                    <!-- Packaged Price Information -->
                    <tr>
                        <td class="hotel-option-label" style="background: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; width: 30%; color: #2c3e50;">Per Adult Packaged Price :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; width: 70%; color: #212529;">{{ is_numeric($hotel['adult_price']) ? number_format($hotel['adult_price'], 2) : ($hotel['adult_price'] ?? 'N/A') }}</td>
                        <td colspan="3" style="background: #ffffff; border: 1px solid #e0e0e0; padding: 4px 6px;"></td>
                    </tr>
                    <tr>
                        <td class="hotel-option-label" style="background: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; color: #2c3e50;">Per Child Packaged Price :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; color: #212529;">{{ is_numeric($hotel['child_price']) ? number_format($hotel['child_price'], 2) : ($hotel['child_price'] ?? 'N/A') }}</td>
                        <td colspan="3" style="background: #ffffff; border: 1px solid #e0e0e0; padding: 4px 6px;"></td>
                    </tr>
                    <tr>
                        <td class="hotel-option-label" style="background: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; color: #2c3e50;">Per Infant Packaged Price :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; color: #212529;">{{ is_numeric($hotel['infant_price']) ? number_format($hotel['infant_price'], 2) : ($hotel['infant_price'] ?? 'N/A') }}</td>
                        <td colspan="3" style="background: #ffffff; border: 1px solid #e0e0e0; padding: 4px 6px;"></td>
                    </tr>
                    <!-- Hotel Details -->
                    <tr>
                        <td class="hotel-option-label" style="background: #f8f9fa; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; color: #2c3e50;">Hotel Name :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; color: #212529;">{{ $hotel['hotel_name'] ?? 'N/A' }}</td>
                        <td colspan="3" style="background: #ffffff; border: 1px solid #e0e0e0; padding: 4px 6px;"></td>
                    </tr>
                    <tr>
                        <td class="hotel-option-label" style="background: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; color: #2c3e50;">Hotel Category :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; color: #212529;">{{ $hotel['hotel_category'] ?? 'N/A' }}</td>
                        <td colspan="3" style="background: #ffffff; border: 1px solid #e0e0e0; padding: 4px 6px;"></td>
                    </tr>
                    <!-- Room Details -->
                    <tr>
                        <td class="hotel-option-label" style="background: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; color: #2c3e50;">No. of Rooms :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0;"></td>
                        <td class="hotel-option-label" style="background: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; text-align: center; color: #2c3e50;">Single</td>
                        <td class="hotel-option-label" style="background: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; text-align: center; color: #2c3e50;">Double</td>
                        <td class="hotel-option-label" style="background: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; text-align: center; color: #2c3e50;">Triple</td>
                    </tr>
                    <!-- Room Categories - Always show exactly 4 rows as per screenshot, directly after No. of Rooms -->
                    @foreach($hotel['room_categories'] as $roomCategory)
                        <tr>
                            <td class="hotel-option-label" style="background: #f0f8ff; font-weight: bold; padding: 4px 6px; border: 1px solid #e0e0e0; color: #2c3e50;">Room Category :</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; color: #212529;">{{ !empty($roomCategory['name']) ? $roomCategory['name'] : 'N/A' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; text-align: center; color: #212529;">{{ is_numeric($roomCategory['single_price']) ? number_format($roomCategory['single_price'], 2) : '0.00' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; text-align: center; color: #212529;">{{ is_numeric($roomCategory['double_price']) ? number_format($roomCategory['double_price'], 2) : '0.00' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; text-align: center; color: #212529;">{{ is_numeric($roomCategory['triple_price']) ? number_format($roomCategory['triple_price'], 2) : '0.00' }}</td>
                        </tr>
                    @endforeach
                    <!-- First Total -->
                    <tr>
                        <td class="hotel-total-row" style="background: #7f8c8d; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #6c7a89;" colspan="2">Total :</td>
                        <td class="hotel-total-row" style="background: #7f8c8d; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #6c7a89;">{{ is_numeric($hotel['first_total']['single']) ? number_format($hotel['first_total']['single'], 2) : '0.00' }}</td>
                        <td class="hotel-total-row" style="background: #7f8c8d; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #6c7a89;">{{ is_numeric($hotel['first_total']['double']) ? number_format($hotel['first_total']['double'], 2) : '0.00' }}</td>
                        <td class="hotel-total-row" style="background: #7f8c8d; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #6c7a89;">{{ is_numeric($hotel['first_total']['triple']) ? number_format($hotel['first_total']['triple'], 2) : '0.00' }}</td>
                    </tr>
                    <!-- Supplemental cost -->
                    <tr>
                        <td class="hotel-supplemental-header" style="background: #e8f5e9; color: #2c3e50; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #c8e6c9;" colspan="2">Supplemental cost :</td>
                        <td class="hotel-supplemental-cell" style="background: #e8f5e9; color: #2c3e50; font-weight: bold; text-align: center; padding: 4px 6px; border: 1px solid #c8e6c9;">Single</td>
                        <td class="hotel-supplemental-cell" style="background: #e8f5e9; color: #2c3e50; font-weight: bold; text-align: center; padding: 4px 6px; border: 1px solid #c8e6c9;">Double</td>
                        <td class="hotel-supplemental-cell" style="background: #e8f5e9; color: #2c3e50; font-weight: bold; text-align: center; padding: 4px 6px; border: 1px solid #c8e6c9;">Triple</td>
                    </tr>
                    <!-- Supplemental cost rows (2 rows as per screenshot) -->
                    @for($i = 1; $i <= 2; $i++)
                        <tr>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; color: #212529;" colspan="2">{{ $i === 1 ? 'N/A' : 'N/A' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; text-align: center; color: #212529;">{{ $i === 1 ? (is_numeric($hotel['supplemental_cost']['single']) ? number_format($hotel['supplemental_cost']['single'], 2) : '0.00') : '0.00' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; text-align: center; color: #212529;">{{ $i === 1 ? (is_numeric($hotel['supplemental_cost']['double']) ? number_format($hotel['supplemental_cost']['double'], 2) : '0.00') : '0.00' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #e0e0e0; text-align: center; color: #212529;">{{ $i === 1 ? (is_numeric($hotel['supplemental_cost']['triple']) ? number_format($hotel['supplemental_cost']['triple'], 2) : '0.00') : '0.00' }}</td>
                        </tr>
                    @endfor
                    <!-- Second Total (Final Total = First Total + Supplemental) -->
                    <tr>
                        <td class="hotel-total-row" style="background: #7f8c8d; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #6c7a89;" colspan="2">Total :</td>
                        <td class="hotel-total-row" style="background: #7f8c8d; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #6c7a89;">{{ is_numeric($hotel['final_total']['single']) ? number_format($hotel['final_total']['single'], 2) : '0.00' }}</td>
                        <td class="hotel-total-row" style="background: #7f8c8d; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #6c7a89;">{{ is_numeric($hotel['final_total']['double']) ? number_format($hotel['final_total']['double'], 2) : '0.00' }}</td>
                        <td class="hotel-total-row" style="background: #7f8c8d; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #6c7a89;">{{ is_numeric($hotel['final_total']['triple']) ? number_format($hotel['final_total']['triple'], 2) : '0.00' }}</td>
                    </tr>
                </table>
            @endforeach
            </div>
    @endif

    @if(empty($servicesByType))
        <div class="empty-state">
            No quotation items have been confirmed for this tour.
        </div>
    @else
        @foreach($servicesByType as $type => $cards)
                @php
                // Skip hotels as they are displayed separately above
                    $normalizedType = str_replace(' ', '_', strtolower($type));
                if ($normalizedType === 'hotel') {
                    continue;
                }
                    $sectionLabel = ucwords(str_replace('_', ' ', $type));
                    if ($normalizedType === 'entry_port') {
                    $sectionLabel = 'Arrival Services';
                    } elseif ($normalizedType === 'exit_port') {
                    $sectionLabel = 'Departure Services';
                } elseif ($normalizedType === 'attraction' || $normalizedType === 'attraction_package') {
                    $sectionLabel = 'Attraction Services';
                } elseif ($normalizedType === 'restaurant') {
                    $sectionLabel = 'Restaurant Services';
                } elseif ($normalizedType === 'guide') {
                    $sectionLabel = 'Guide Services';
                } elseif (in_array($normalizedType, ['travel_point', 'travel_hourly', 'local_transport', 'local_transfer', 'point_to_point', 'hourly'])) {
                    $sectionLabel = 'Transfer Services';
                } else {
                    $sectionLabel = ucwords(str_replace('_', ' ', $type)) . ' Services';
                    }
                @endphp
            <div class="section-title">{{ $sectionLabel }}</div>
            <div style="page-break-inside: auto;">
            @if($normalizedType === 'entry_port')
                <!-- Arrival Services (Excel-like format with two-row tables) -->
                    @foreach($cards as $card)
                    @php
                        $pickup = '';
                        $dropoff = '';
                        $pickupDate = '';
                        $entryTime = '';
                        
                        foreach ($card['chips'] ?? [] as $chip) {
                            if (strtolower($chip['label']) === 'pickup') {
                                $pickup = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'dropoff') {
                                $dropoff = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'date') {
                                $pickupDate = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'time') {
                                $entryTime = $chip['value'];
                            }
                        }
                        
                        $vehicleData = $card['vehicle'] ?? [];
                        $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? 'N/A';
                        // Format transfer type: remove underscores, capitalize words
                        if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                            $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                        } else {
                            $transferType = $transferTypeRaw;
                        }
                        $vehicleTypeSeater = $vehicleData['vehicle_type_seater'] ?? 'N/A';
                        $vehicleNumber = $vehicleData['vehicle_number'] ?? 'N/A';
                        $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
                        $maxPassengerWithLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
                        $maxLuggageCapacity = 'N/A'; // Not typically stored
                        $maxPassengerWithoutLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
                        
                        // Port Name - typically from entrypickup (airport name)
                        $portName = $pickup ?: 'N/A';
                        
                        // Flight details - extract from entry_port_flight data
                        $flightData = $card['entry_port_flight'] ?? [];
                        $flightName = $flightData['flight_name'] ?? 'TBA';
                        $flightNo = $flightData['flight_no'] ?? 'TBA';
                        $originDepartureTime = $flightData['origin_departure_time'] ?? 'TBA';
                        $originDepartureTerminal = $flightData['origin_departure_terminal'] ?? 'TBA';
                        $destinationArrivalTime = $flightData['destination_arrival_time'] ?? ($entryTime ?: 'TBA');
                        $destinationArrivalTerminal = $flightData['destination_arrival_terminal'] ?? 'TBA';
                                @endphp
                    
                    <!-- First Table: Port of Arrival Transfer - Parameters 1-6 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 10px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th colspan="6" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; padding: 6px; border: 1px solid #90a0b0; text-align: center; font-size: 12px;">
                                    Port of Arrival Transfer :
                                </th>
                            </tr>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Port Name :</th>
                                <th style="width: 16.67%;">Transfer Type :</th>
                                <th style="width: 16.67%;">Vehicle Type / Seater :</th>
                                <th style="width: 16.67%;">Vehicle No :</th>
                                <th style="width: 16.67%;">Vehicle Brand :</th>
                                <th style="width: 16.67%;">Max Passenger capacity with luggage :</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $portName }}</td>
                                <td>{{ $transferType }}</td>
                                <td>{{ $vehicleTypeSeater }}</td>
                                <td>{{ $vehicleNumber }}</td>
                                <td>{{ $vehicleBrand }}</td>
                                <td>{{ $maxPassengerWithLuggage }}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Second Table: Port of Arrival Transfer - Parameters 7-8 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 10px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Max Luggage capacity :</th>
                                <th style="width: 16.67%;">Max Passenger Capacity without luggage :</th>
                                <th colspan="4" style="background-color: #ffffff;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $maxLuggageCapacity }}</td>
                                <td>{{ $maxPassengerWithoutLuggage }}</td>
                                <td colspan="4"></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Third Table: Flight Details - Parameters 1-6 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 20px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th colspan="6" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; padding: 6px; border: 1px solid #90a0b0; text-align: center; font-size: 12px;">
                                    Flight Details :
                                </th>
                            </tr>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Flight Name :</th>
                                <th style="width: 16.67%;">Flight No. :</th>
                                <th style="width: 16.67%;">Origin Departure Time :</th>
                                <th style="width: 16.67%;">Origin Departure Terminal :</th>
                                <th style="width: 16.67%;">Destination Arrival Time :</th>
                                <th style="width: 16.67%;">Destination Arrival Terminal :</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $flightName }}</td>
                                <td>{{ $flightNo }}</td>
                                <td>{{ $originDepartureTime }}</td>
                                <td>{{ $originDepartureTerminal }}</td>
                                <td>{{ $destinationArrivalTime }}</td>
                                <td>{{ $destinationArrivalTerminal }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            @elseif($normalizedType === 'exit_port')
                <!-- Departure Services (Excel-like format with two-row tables) -->
                @foreach($cards as $card)
                    @php
                        $pickup = '';
                        $dropoff = '';
                        $pickupDate = '';
                        $exitTime = '';
                        
                        foreach ($card['chips'] ?? [] as $chip) {
                            if (strtolower($chip['label']) === 'pickup') {
                                $pickup = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'dropoff') {
                                $dropoff = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'date') {
                                $pickupDate = $chip['value'];
                            }
                            if (strtolower($chip['label']) === 'time') {
                                $exitTime = $chip['value'];
                            }
                        }
                        
                        $vehicleData = $card['vehicle'] ?? [];
                        $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? 'N/A';
                        // Format transfer type: remove underscores, capitalize words
                        if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                            $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                        } else {
                            $transferType = $transferTypeRaw;
                        }
                        $vehicleTypeSeater = $vehicleData['vehicle_type_seater'] ?? 'N/A';
                        $vehicleNumber = $vehicleData['vehicle_number'] ?? 'N/A';
                        $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
                        $maxPassengerWithLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
                        $maxLuggageCapacity = 'N/A'; // Not typically stored
                        $maxPassengerWithoutLuggage = $vehicleData['max_passenger_capacity'] ?? 'N/A';
                        
                        // Port Name - typically from exitdropoff (airport name)
                        $portName = $dropoff ?: 'N/A';
                        
                        // Flight details - extract from exit_port_flight data
                        $flightData = $card['exit_port_flight'] ?? [];
                        $flightName = $flightData['flight_name'] ?? 'TBA';
                        $flightNo = $flightData['flight_no'] ?? 'TBA';
                        $originDepartureTime = $flightData['origin_departure_time'] ?? ($exitTime ?: 'TBA');
                        $originDepartureTerminal = $flightData['origin_departure_terminal'] ?? 'TBA';
                        $destinationArrivalTime = $flightData['destination_arrival_time'] ?? 'TBA';
                        $destinationArrivalTerminal = $flightData['destination_arrival_terminal'] ?? 'TBA';
                                        @endphp
                    
                    <!-- First Table: Port of Departure Transfer - Parameters 1-6 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 10px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th colspan="6" style="background-color: #a0aec0; color: #2c3e50; font-weight: bold; padding: 6px; border: 1px solid #90a0b0; text-align: center; font-size: 12px;">
                                    Port of Departure Transfer :
                                </th>
                            </tr>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Port Name :</th>
                                <th style="width: 16.67%;">Transfer Type :</th>
                                <th style="width: 16.67%;">Vehicle Type / Seater :</th>
                                <th style="width: 16.67%;">Vehicle No :</th>
                                <th style="width: 16.67%;">Vehicle Brand :</th>
                                <th style="width: 16.67%;">Max Passenger capacity with luggage :</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $portName }}</td>
                                <td>{{ $transferType }}</td>
                                <td>{{ $vehicleTypeSeater }}</td>
                                <td>{{ $vehicleNumber }}</td>
                                <td>{{ $vehicleBrand }}</td>
                                <td>{{ $maxPassengerWithLuggage }}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Second Table: Port of Departure Transfer - Parameters 7-8 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 10px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Max Luggage capacity :</th>
                                <th style="width: 16.67%;">Max Passenger Capacity without luggage :</th>
                                <th colspan="4" style="background-color: #ffffff;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $maxLuggageCapacity }}</td>
                                <td>{{ $maxPassengerWithoutLuggage }}</td>
                                <td colspan="4"></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Third Table: Flight Details - Parameters 1-6 -->
                    <table style="width: 100%; border-collapse: collapse; margin: 0 0 20px 0; page-break-inside: auto;">
                        <thead>
                            <tr style="page-break-inside: avoid;">
                                <th colspan="6" style="background-color: #6c7a89; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #5a6c7d; text-align: center; font-size: 14px;">
                                    Flight Details :
                                </th>
                            </tr>
                            <tr style="page-break-inside: avoid;">
                                <th style="width: 16.67%;">Flight Name :</th>
                                <th style="width: 16.67%;">Flight No. :</th>
                                <th style="width: 16.67%;">Origin Departure Time :</th>
                                <th style="width: 16.67%;">Origin Departure Terminal :</th>
                                <th style="width: 16.67%;">Destination Arrival Time :</th>
                                <th style="width: 16.67%;">Destination Arrival Terminal :</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $flightName }}</td>
                                <td>{{ $flightNo }}</td>
                                <td>{{ $originDepartureTime }}</td>
                                <td>{{ $originDepartureTerminal }}</td>
                                <td>{{ $destinationArrivalTime }}</td>
                                <td>{{ $destinationArrivalTerminal }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            @elseif($normalizedType === 'attraction' || $normalizedType === 'attraction_package')
                <!-- Attraction Services Table -->
                <table style="margin-bottom: 20px; page-break-inside: auto;">
                    <thead>
                        <tr style="page-break-inside: avoid;">
                            <th>Attraction Name</th>
                            <th>Attraction Timing</th>
                            <th>Transfer</th>
                            <th>Transfer Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cards as $card)
                            @php
                                $attractionData = $card['attraction'] ?? [];
                                
                                // Get Attraction Timing from visit_time
                                $attractionTiming = $attractionData['visit_time'] ?? 'N/A';
                                
                                // Get Transfer (Yes/No)
                                $transferRequired = $attractionData['transfer_required'] ?? 'N/A';
                                
                                // Get Transfer Type and format it
                                $transferTypeRaw = $attractionData['transfer_type'] ?? 'N/A';
                                if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                                    $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                                                    } else {
                                    $transferType = $transferTypeRaw;
                                                    }
                                                @endphp
                            <tr>
                                <td>{{ $card['title'] ?? 'N/A' }}</td>
                                <td>{{ $attractionTiming }}</td>
                                <td>{{ $transferRequired }}</td>
                                <td>{{ $transferType }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif($normalizedType === 'restaurant')
                <!-- Restaurant Services Table -->
                <table style="margin-bottom: 20px; page-break-inside: auto;">
                    <thead>
                        <tr style="page-break-inside: avoid;">
                            <th>Restaurant Name</th>
                            <th>Meal Plan</th>
                            <th>Meal Type</th>
                            <th>Transfer</th>
                            <th>Transfer Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cards as $card)
                            @php
                                $restaurantData = $card['restaurant'] ?? [];
                                
                                // Get Meal Plan from meal_plan
                                $mealPlan = $restaurantData['meal_plan'] ?? 'N/A';
                                
                                // Get Meal Type
                                $mealType = $restaurantData['meal_type'] ?? 'N/A';
                                
                                // Get Transfer (Yes/No)
                                $transferRequired = $restaurantData['transfer_required'] ?? 'N/A';
                                
                                // Get Transfer Type and format it
                                $transferTypeRaw = $restaurantData['transfer_type'] ?? 'N/A';
                                if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                                    $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                                } else {
                                    $transferType = $transferTypeRaw;
                                                                }
                                                            @endphp
                            <tr>
                                <td>{{ $card['title'] ?? 'N/A' }}</td>
                                <td>{{ $mealPlan }}</td>
                                <td>{{ $mealType }}</td>
                                <td>{{ $transferRequired }}</td>
                                <td>{{ $transferType }}</td>
                            </tr>
                                                    @endforeach
                    </tbody>
                </table>
            @elseif($normalizedType === 'guide')
                <!-- Guide Services Table -->
                <table style="margin-bottom: 20px; page-break-inside: auto;">
                    <thead>
                        <tr style="page-break-inside: avoid;">
                            <th>Tour Guide Name</th>
                            <th>Language Proficiency</th>
                            <th>Total Experience</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cards as $card)
                            @php
                                $guideData = $card['guide'] ?? [];
                                
                                // Get Tour Guide Name
                                $guideName = $guideData['guide_name'] ?? $card['title'] ?? 'N/A';
                                
                                // Get Language Proficiency
                                $languageProficiency = $guideData['language_proficiency'] ?? 'N/A';
                                
                                // Get Total Experience
                                $totalExperience = $guideData['total_experience'] ?? 'N/A';
                                @endphp
                            <tr>
                                <td>{{ $guideName }}</td>
                                <td>{{ $languageProficiency }}</td>
                                <td>{{ $totalExperience }}</td>
                            </tr>
                                                    @endforeach
                    </tbody>
                </table>
            @elseif(in_array($normalizedType, ['travel_point', 'travel_hourly', 'local_transport', 'local_transfer', 'point_to_point', 'hourly']))
                <!-- Transfer Services Table (Point to Point / Hourly / Local Transport) -->
                <table style="margin-bottom: 20px; page-break-inside: auto;">
                    <thead>
                        <tr style="page-break-inside: avoid;">
                            <th>Transfer Type</th>
                            <th>Vehicle Type / Seater</th>
                            <th>Vehicle No</th>
                            <th>Vehicle Brand</th>
                            <th>Max Passenger Capacity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cards as $card)
                            @php
                                $vehicleData = $card['vehicle'] ?? [];
                                
                                // Get Transfer Type and format it (remove underscores, capitalize words)
                                $transferTypeRaw = $vehicleData['transfer_type'] ?? 'N/A';
                                if ($transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                                    $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                                } else {
                                    $transferType = $transferTypeRaw;
                                }
                                
                                // Get Vehicle Type / Seater
                                $vehicleTypeSeater = $vehicleData['vehicle_type_seater'] ?? 'N/A';
                                
                                // Get Vehicle No
                                $vehicleNumber = $vehicleData['vehicle_number'] ?? 'N/A';
                                
                                // Get Vehicle Brand
                                $vehicleBrand = $vehicleData['vehicle_brand'] ?? 'N/A';
                                
                                // Get Max Passenger Capacity
                                $maxPassengerCapacity = $vehicleData['max_passenger_capacity'] ?? $vehicleData['seating_capacity'] ?? 'N/A';
                                                @endphp
                            <tr>
                                <td>{{ $transferType }}</td>
                                <td>{{ $vehicleTypeSeater }}</td>
                                <td>{{ $vehicleNumber }}</td>
                                <td>{{ $vehicleBrand }}</td>
                                <td>{{ $maxPassengerCapacity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <!-- Generic Services Table (fallback) -->
                <table style="margin-bottom: 20px; page-break-inside: auto;">
                    <thead>
                        <tr style="page-break-inside: avoid;">
                            <th>Service Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Details</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cards as $card)
                            @php
                                $dateValue = '';
                                $timeValue = '';
                                foreach ($card['chips'] ?? [] as $chip) {
                                    if (strtolower($chip['label']) === 'date') {
                                        $dateValue = $chip['value'];
                                    }
                                    if (strtolower($chip['label']) === 'time') {
                                        $timeValue = $chip['value'];
                                                        }
                                                    }
                                                @endphp
                            <tr>
                                <td>{{ $card['title'] ?? 'N/A' }}</td>
                                <td>{{ $dateValue ?: 'N/A' }}</td>
                                <td>{{ $timeValue ?: 'N/A' }}</td>
                                <td>{{ $card['subtitle'] ?? 'N/A' }}</td>
                                <td>{{ $card['notes'] ?? 'N/A' }}</td>
                                <td>{{ $card['notes'] ?? 'N/A' }}</td>
                            </tr>
                                                    @endforeach
                    </tbody>
                </table>
                                            @endif
                                        </div>
                                                    @endforeach
                                            @endif

    <!-- Terms & Conditions Section -->
    <div style="margin-top: 30px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse; border: 2px solid #000000;">
            <thead>
                <tr>
                    <th colspan="2" style="background-color: #ffb6c1; color: #000000; font-weight: bold; padding: 10px; border: 2px solid #000000; text-align: left; font-size: 14px;">
                        Terms & Conditions :
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="background-color: #ffffff; padding: 20px; border: 2px solid #000000; min-height: 100px; vertical-align: top;">
                        {{ $termsAndConditions ?: '' }}
                    </td>
                </tr>
            </tbody>
        </table>
                                    </div>

    <!-- Important Notes/Disclaimers -->
    <div style="margin-top: 15px; margin-bottom: 15px;">
        <p style="color: #ff0000; font-size: 11px; margin: 5px 0;">
            *Please note that this is not a tour itinerary / schedule, a confirmed tour itinerary / schedule is only generated post confirmation of the tour and payment is completed.
        </p>
        <p style="color: #ff0000; font-size: 11px; margin: 5px 0;">
            *The above quotation only specifies the optionwise costs based on the tour requirements with standard exclusions & Inclusions as mentioned above.
        </p>
                            </div>

    <!-- Payment Terms Section -->
    <div style="margin-top: 20px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse; border: 2px solid #000000;">
            <thead>
                <tr>
                    <th colspan="2" style="background-color: #ffb6c1; color: #000000; font-weight: bold; padding: 10px; border: 2px solid #000000; text-align: left; font-size: 14px;">
                        Payment Terms :
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="background-color: #ffffff; padding: 20px; border: 2px solid #000000; min-height: 100px; vertical-align: top;">
                        @if(!empty($paymentTerms) && is_array($paymentTerms))
                            <ol style="margin-left: 20px; margin-top: 5px; padding-left: 20px;">
                                @foreach($paymentTerms as $term)
                                    <li style="margin-bottom: 5px;">{{ $term }}</li>
                    @endforeach
                            </ol>
                        @else
                            
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
                </div>

    <!-- Bank Details Section -->
    <div style="margin-top: 20px; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse; border: 2px solid #000000;">
            <thead>
                <tr>
                    <th colspan="2" style="background-color: #ffb6c1; color: #000000; font-weight: bold; padding: 10px; border: 2px solid #000000; text-align: left; font-size: 14px;">
                        Bank Details :
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background-color: #f0f8ff; font-weight: bold; padding: 6px; border: 1px solid #000000; width: 40%;">Account Name :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['account_name'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">Account Number :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['account_number'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">Bank Address :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['bank_address'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">IFSC (For India only) :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['ifsc'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">SWIFT / BIC / IBAN Code (as applicable for international) :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['swift_bic_iban'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">Bank Code (For Singapore) :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['bank_code'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">Branch Code (For Singapore) :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['branch_code'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; font-weight: bold; padding: 6px; border: 1px solid #000000;">ABA / Routing Number (For USA only) :</td>
                    <td style="background-color: #ffffff; padding: 6px; border: 1px solid #000000;">{{ $bankDetails['aba_routing'] ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
            </div>
    
            </div>
</body>
</html>

