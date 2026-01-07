<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tour Quotation</title>
    <style>
        * {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #000000;
        }
        /* Excel-like Quotation Page Styles - Simple table format */
        .quotation-page {
            page-break-after: always;
            background: #ffffff;
            padding: 0;
            margin: 0;
            width: 100%;
            min-height: 100vh;
        }
        @page {
            margin: 0;
            padding: 0;
        }
        /* Content wrapper for pages after quotation */
        .content-wrapper {
            padding: 24px;
            background: #f3f5fb;
        }
        .quotation-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000000;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .quotation-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
            vertical-align: top;
            margin: 0;
        }
        .quotation-table tr {
            margin: 0;
        }
        .quotation-label {
            background: #d3d3d3;
            font-weight: bold;
            padding: 4px 6px;
            border: 1px solid #000000;
        }
        .quotation-value {
            background: #ffffff;
            padding: 4px 6px;
            border: 1px solid #000000;
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
            border: 2px solid #000000;
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
            border: 1px solid #000000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .hotel-option-header {
            background: #000000;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 10px;
        }
        .hotel-option-label {
            background: #d3d3d3;
            font-weight: bold;
            padding: 4px 6px;
        }
        .hotel-option-value {
            background: #ffffff;
            padding: 4px 6px;
        }
        .hotel-total-row {
            background: #000000;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 6px;
        }
        .hotel-supplemental-header {
            background: #90EE90;
            color: #000000;
            font-weight: bold;
            text-align: center;
            padding: 6px;
        }
        .hotel-supplemental-cell {
            background: #90EE90;
            color: #000000;
            font-weight: bold;
            text-align: center;
            padding: 4px 6px;
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
            background: #1e40af;
            color: #ffffff;
            padding: 18px 20px;
            border-radius: 12px 12px 0 0;
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .08em;
            border: 2px solid #1e3a8a;
            border-bottom: none;
        }
        .price-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0;
            background: #ffffff;
            border: 3px solid #1e40af;
            border-top: none;
            border-radius: 0 0 12px 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.15);
        }
        .price-summary-item {
            padding: 24px 16px;
            text-align: center;
            border-right: 2px solid #3b82f6;
            background: #f8fafc;
        }
        .price-summary-item:last-child {
            border-right: none;
        }
        .price-summary-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #1e293b;
            margin-bottom: 12px;
            font-weight: 700;
        }
        .price-summary-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e40af;
            line-height: 1.2;
        }
        .price-summary-currency {
            font-size: 20px;
            vertical-align: top;
            margin-right: 3px;
            color: #1e40af;
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

    <!-- Excel-like Quotation First Page -->
    <div class="quotation-page">
        <table class="quotation-table" style="width: 100%; border-collapse: collapse; border: 2px solid #000000; margin: 0; padding: 0;">
            <!-- Header Row: QUOTATION -->
            <tr>
                <td colspan="6" style="background: #000000; color: #ffffff; text-align: center; font-weight: bold; font-size: 16px; padding: 10px; border: 1px solid #000000;">
                    QUOTATION
                </td>
            </tr>
            <!-- Subheader Row: Name of The DMC -->
            <tr>
                <td colspan="6" style="background: #000000; color: #ffffff; text-align: center; font-weight: bold; font-size: 12px; padding: 8px; border: 1px solid #000000;">
                    {{ $dmcDetails['company_name'] ?? 'N/A' }}
                    
                </td>
            </tr>
            <!-- DMC Contact Information and Proposal Details Row -->
            <tr>
                <!-- Left Column: DMC Contact Information -->
                <td style="width: 25%; vertical-align: top; border: 1px solid #000000; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000; width: 40%;">Address :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; width: 60%;">{{ $dmcDetails['address'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">City :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $dmcDetails['city'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Country :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $dmcDetails['country'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Email :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $dmcDetails['email'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Email :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $dmcDetails['email2'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Phone :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $dmcDetails['phone'] ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </td>
                <!-- Middle Column: Proposal Details -->
                <td style="width: 25%; vertical-align: top; border: 1px solid #000000; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000; width: 40%;">Postal / Pin :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; width: 60%;">{{ $dmcDetails['postal_pin'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Proposal Date :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $proposalDetails['proposal_date'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Proposal Validity :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $proposalDetails['proposal_validity'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Proposal Sent by :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $proposalDetails['proposal_sent_by'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; height: 20px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; height: 20px;">&nbsp;</td>
                        </tr>
                    </table>
                </td>
                <!-- Right Column: Logo Area and Travel Company/Agent -->
                <td colspan="4" style="width: 50%; vertical-align: top; border: 1px solid #000000; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                        <!-- Logo Area Row - Full width at top -->
                        <tr>
                            <td colspan="2" style="background: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000; height: 120px; padding: 4px;">
                                @if(!empty($dmcLogo) && strpos($dmcLogo, 'data:image') === 0)
                                    <img src="{{ $dmcLogo }}" style="max-width: 180px; max-height: 100px; object-fit: contain;" />
                                @else
                                    &nbsp;
                                @endif
                            </td>
                        </tr>
                        <!-- Travel Company/Agent Header -->
                        <tr>
                            <td colspan="2" style="background: #d3d3d3; font-weight: bold; text-align: left; padding: 4px 6px; border: 1px solid #000000;">
                                Travel Company / Agent Name : {{ $agentDetails['name'] ?? 'N/A' }}
                            </td>
                        </tr>
                        <!-- Travel Company/Agent Details -->
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000; width: 40%;">Address :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; width: 60%;">{{ $agentDetails['address'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Contact Person :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $agentDetails['contact_person'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Phone :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $agentDetails['phone'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Email :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $agentDetails['email'] ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- Booking and Guest Details Row -->
            <tr>
                <td colspan="2" style="vertical-align: top; border: 1px solid #000000; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000; width: 40%;">Booking ID :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; width: 60%;">{{ $bookingDetails['booking_id'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Lead Guest Name :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $bookingDetails['lead_guest_name'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">No. of adults :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $bookingDetails['no_of_adults'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">No. of Children :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $bookingDetails['no_of_children'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">No. of Infants :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $bookingDetails['no_of_infants'] ?? 0 }}</td>
                        </tr>
                    </table>
                </td>
                <td colspan="4" style="background: #ffffff; border: 1px solid #000000; padding: 4px;">&nbsp;</td>
            </tr>
            <!-- Travel Details Row -->
            <tr>
                <td colspan="6" style="border: 1px solid #000000; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                        <tr>
                            <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000; width: 15%;">Destinaton :</td>
                            <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; width: 35%;">{{ $travelDetails['destination'] ?? 'N/A' }}</td>
                            <td colspan="4" style="background: #ffffff; border: 1px solid #000000; padding: 0;">
                                <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                                    <tr>
                                        <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000; width: 30%;">Travel Date :</td>
                                        <td colspan="3" style="background: #ffffff; border: 1px solid #000000; padding: 4px 6px;">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">From</td>
                                        <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $travelDetails['travel_date_from'] ?? 'N/A' }}</td>
                                        <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">To</td>
                                        <td style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $travelDetails['travel_date_to'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Duration / No of Days:</td>
                                        <td colspan="3" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $travelDetails['duration'] ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Existing Content (Itinerary Details) -->
    <div class="content-wrapper">
    <div class="header-card">
        @if(!empty($dmcCompanyName) || (!empty($dmcLogo) && strpos($dmcLogo, 'data:image') === 0))
            <div class="header-top">
                @if(!empty($dmcLogo) && strpos($dmcLogo, 'data:image') === 0)
                    <img src="{{ $dmcLogo }}" class="dmc-logo" style="display:block;max-width:120px;max-height:60px;object-fit:contain;" />
                @endif
                @if(!empty($dmcCompanyName))
                    <div class="dmc-company-name">{{ $dmcCompanyName }}</div>
                @endif
            </div>
        @endif
        <span class="badge">Tour Quotation</span>
        <h1>{{ $tour->display_id ?? ('Tour #' . ($tour->tour_id ?? '-')) }}</h1>
        <p style="margin:0;color:#64748b;font-size:14px;">Generated on {{ $generatedAt->format('d M Y, H:i') }}</p>

        <div class="meta-grid">
            <div class="meta-tile">
                <span>Destination</span>
                <strong>{{ $tour->destination ?? 'Not specified' }}</strong>
            </div>
            <div class="meta-tile">
                <span>Travel Window</span>
                <strong>{{ $checkIn }} - {{ $checkOut }}</strong>
            </div>
            <div class="meta-tile">
                <span>Confirmed Services</span>
                <strong>{{ $totalServices }}</strong>
            </div>
        </div>
    </div>

    <!-- Hotel Options Section (Excel-like format) -->
    @if(!empty($hotelOptions) && count($hotelOptions) > 0)
        <div class="hotel-options-section">
            @foreach($hotelOptions as $hotel)
                <table class="hotel-option-table" style="width: 100%; border-collapse: collapse; border: 2px solid #000000; font-size: 11px; margin: 0 0 20px 0; padding: 0;">
                    <!-- OPTION Header -->
                    <tr>
                        <td colspan="5" class="hotel-option-header" style="background: #000000; color: #ffffff; text-align: center; font-weight: bold; font-size: 14px; padding: 10px; border: 1px solid #000000;">
                            OPTION {{ $hotel['option_number'] }}
                        </td>
                    </tr>
                    <!-- Packaged Price Information -->
                    <tr>
                        <td class="hotel-option-label" style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000; width: 30%;">Per Adult Packaged Price :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; width: 70%;">{{ is_numeric($hotel['adult_price']) ? number_format($hotel['adult_price'], 2) : ($hotel['adult_price'] ?? 'N/A') }}</td>
                        <td colspan="3" style="background: #ffffff; border: 1px solid #000000; padding: 4px 6px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="hotel-option-label" style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Per Child Packaged Price :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ is_numeric($hotel['child_price']) ? number_format($hotel['child_price'], 2) : ($hotel['child_price'] ?? 'N/A') }}</td>
                        <td colspan="3" style="background: #ffffff; border: 1px solid #000000; padding: 4px 6px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="hotel-option-label" style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Per Infant Packaged Price :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ is_numeric($hotel['infant_price']) ? number_format($hotel['infant_price'], 2) : ($hotel['infant_price'] ?? 'N/A') }}</td>
                        <td colspan="3" style="background: #ffffff; border: 1px solid #000000; padding: 4px 6px;">&nbsp;</td>
                    </tr>
                    <!-- Hotel Details -->
                    <tr>
                        <td class="hotel-option-label" style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Hotel Name :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $hotel['hotel_name'] ?? 'N/A' }}</td>
                        <td colspan="3" style="background: #ffffff; border: 1px solid #000000; padding: 4px 6px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="hotel-option-label" style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Hotel Category :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ $hotel['hotel_category'] ?? 'N/A' }}</td>
                        <td colspan="3" style="background: #ffffff; border: 1px solid #000000; padding: 4px 6px;">&nbsp;</td>
                    </tr>
                    <!-- Room Details -->
                    <tr>
                        <td class="hotel-option-label" style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">No. of Rooms :</td>
                        <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">&nbsp;</td>
                        <td class="hotel-option-label" style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000; text-align: center;">Single</td>
                        <td class="hotel-option-label" style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000; text-align: center;">Double</td>
                        <td class="hotel-option-label" style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000; text-align: center;">Triple</td>
                    </tr>
                    <!-- Room Categories - Always show exactly 4 rows as per screenshot, directly after No. of Rooms -->
                    @foreach($hotel['room_categories'] as $roomCategory)
                        <tr>
                            <td class="hotel-option-label" style="background: #d3d3d3; font-weight: bold; padding: 4px 6px; border: 1px solid #000000;">Room Category :</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;">{{ !empty($roomCategory['name']) ? $roomCategory['name'] : 'N/A' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; text-align: center;">{{ is_numeric($roomCategory['single_price']) ? number_format($roomCategory['single_price'], 2) : '0.00' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; text-align: center;">{{ is_numeric($roomCategory['double_price']) ? number_format($roomCategory['double_price'], 2) : '0.00' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; text-align: center;">{{ is_numeric($roomCategory['triple_price']) ? number_format($roomCategory['triple_price'], 2) : '0.00' }}</td>
                        </tr>
                    @endforeach
                    <!-- First Total -->
                    <tr>
                        <td class="hotel-total-row" style="background: #000000; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #000000;" colspan="2">Total :</td>
                        <td class="hotel-total-row" style="background: #000000; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #000000;">{{ is_numeric($hotel['first_total']['single']) ? number_format($hotel['first_total']['single'], 2) : '0.00' }}</td>
                        <td class="hotel-total-row" style="background: #000000; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #000000;">{{ is_numeric($hotel['first_total']['double']) ? number_format($hotel['first_total']['double'], 2) : '0.00' }}</td>
                        <td class="hotel-total-row" style="background: #000000; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #000000;">{{ is_numeric($hotel['first_total']['triple']) ? number_format($hotel['first_total']['triple'], 2) : '0.00' }}</td>
                    </tr>
                    <!-- Supplemental cost -->
                    <tr>
                        <td class="hotel-supplemental-header" style="background: #90EE90; color: #000000; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #000000;" colspan="2">Supplemental cost :</td>
                        <td class="hotel-supplemental-cell" style="background: #90EE90; color: #000000; font-weight: bold; text-align: center; padding: 4px 6px; border: 1px solid #000000;">Single</td>
                        <td class="hotel-supplemental-cell" style="background: #90EE90; color: #000000; font-weight: bold; text-align: center; padding: 4px 6px; border: 1px solid #000000;">Double</td>
                        <td class="hotel-supplemental-cell" style="background: #90EE90; color: #000000; font-weight: bold; text-align: center; padding: 4px 6px; border: 1px solid #000000;">Triple</td>
                    </tr>
                    <!-- Supplemental cost rows (2 rows as per screenshot) -->
                    @for($i = 1; $i <= 2; $i++)
                        <tr>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000;" colspan="2">{{ $i === 1 ? 'N/A' : 'N/A' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; text-align: center;">{{ $i === 1 ? (is_numeric($hotel['supplemental_cost']['single']) ? number_format($hotel['supplemental_cost']['single'], 2) : '0.00') : '0.00' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; text-align: center;">{{ $i === 1 ? (is_numeric($hotel['supplemental_cost']['double']) ? number_format($hotel['supplemental_cost']['double'], 2) : '0.00') : '0.00' }}</td>
                            <td class="hotel-option-value" style="background: #ffffff; padding: 4px 6px; border: 1px solid #000000; text-align: center;">{{ $i === 1 ? (is_numeric($hotel['supplemental_cost']['triple']) ? number_format($hotel['supplemental_cost']['triple'], 2) : '0.00') : '0.00' }}</td>
                        </tr>
                    @endfor
                    <!-- Second Total (Final Total = First Total + Supplemental) -->
                    <tr>
                        <td class="hotel-total-row" style="background: #000000; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #000000;" colspan="2">Total :</td>
                        <td class="hotel-total-row" style="background: #000000; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #000000;">{{ is_numeric($hotel['final_total']['single']) ? number_format($hotel['final_total']['single'], 2) : '0.00' }}</td>
                        <td class="hotel-total-row" style="background: #000000; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #000000;">{{ is_numeric($hotel['final_total']['double']) ? number_format($hotel['final_total']['double'], 2) : '0.00' }}</td>
                        <td class="hotel-total-row" style="background: #000000; color: #ffffff; font-weight: bold; text-align: center; padding: 6px; border: 1px solid #000000;">{{ is_numeric($hotel['final_total']['triple']) ? number_format($hotel['final_total']['triple'], 2) : '0.00' }}</td>
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
            @endphp
            <div class="section">
                @php
                    $normalizedType = str_replace(' ', '_', strtolower($type));
                    $sectionLabel = ucwords(str_replace('_', ' ', $type));
                    if ($normalizedType === 'entry_port') {
                        $sectionLabel = 'Arrival';
                    } elseif ($normalizedType === 'exit_port') {
                        $sectionLabel = 'Departure';
                    }
                @endphp
                <div class="section-header">
                    <h3>{{ $sectionLabel }}</h3>
                    <span>{{ count($cards) }} service{{ count($cards) > 1 ? 's' : '' }}</span>
                </div>
                <div class="card-grid">
                    @foreach($cards as $card)
                        <div class="service-card">
                            <div class="service-icon">{{ $card['icon'] }}</div>
                            <div class="service-body">
                                <div class="service-title">{{ $card['title'] }}</div>
                                @if(!empty($card['subtitle']))
                                    <div class="service-subtitle">{{ $card['subtitle'] }}</div>
                                @endif
                                <div class="chip-row">
                                    @foreach($card['chips'] as $chip)
                                        <div class="chip">{{ $chip['label'] }}: {{ $chip['value'] }}</div>
                                    @endforeach
                                </div>
                                @php
                                    $vehicleData = isset($card['vehicle']) && is_array($card['vehicle']) ? $card['vehicle'] : [];
                                    $hasVehicle = count(array_filter($vehicleData)) > 0;
                                @endphp
                                @if($hasVehicle)
                                    <div class="vehicle-block">
                                        @if(!empty($vehicleData['name']))
                                            <div class="vehicle-line">Vehicle: {{ $vehicleData['name'] }}</div>
                                        @endif
                                        @php
                                            $vehicleMeta = array_filter([
                                                !empty($vehicleData['type']) ? 'Service Type: ' . $vehicleData['type'] : null,
                                                !empty($vehicleData['vehicle_model']) ? 'Model: ' . $vehicleData['vehicle_model'] : null,
                                                !empty($vehicleData['model_year']) ? 'Year: ' . $vehicleData['model_year'] : null,
                                            ]);
                                        @endphp
                                        @if(!empty($vehicleMeta))
                                            <div class="vehicle-meta">{{ implode(' • ', $vehicleMeta) }}</div>
                                        @endif
                                        <div class="vehicle-chips">
                                            @if(!empty($vehicleData['vehicle_type']))
                                                <span class="vehicle-chip">Category: {{ $vehicleData['vehicle_type'] }}</span>
                                            @endif
                                            @if(!empty($vehicleData['seating_capacity']))
                                                <span class="vehicle-chip">Seats: {{ $vehicleData['seating_capacity'] }}</span>
                                            @endif
                                            @if(!empty($vehicleData['travel_type']))
                                                @php
                                                    $travelLabel = '';
                                                    if (strtolower($vehicleData['travel_type']) === 'entry_port') {
                                                        $travelLabel = 'Arrival';
                                                    } elseif (strtolower($vehicleData['travel_type']) === 'exit_port') {
                                                        $travelLabel = 'Departure';
                                                    } else {
                                                        $travelLabel = ucwords(str_replace('_', ' ', $vehicleData['travel_type']));
                                                    }
                                                @endphp
                                                <span class="vehicle-chip">{{ $travelLabel }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @php
                                    $attractionData = isset($card['attraction']) && is_array($card['attraction']) ? $card['attraction'] : [];
                                    $hasAttraction = count(array_filter($attractionData)) > 0;
                                @endphp
                                @if($hasAttraction)
                                    <div class="detail-block">
                                        @if(!empty($attractionData['ticket_name']))
                                            <div class="detail-line">Ticket: {{ $attractionData['ticket_name'] }}</div>
                                        @endif
                                        <div class="detail-chips">
                                            @if(!empty($attractionData['adult_count']))
                                                <span class="detail-chip">Adults: {{ $attractionData['adult_count'] }}</span>
                                            @endif
                                            @if(!empty($attractionData['child_count']))
                                                <span class="detail-chip">Children: {{ $attractionData['child_count'] }}</span>
                                            @endif
                                            @if(!empty($attractionData['senior_count']))
                                                <span class="detail-chip">Seniors: {{ $attractionData['senior_count'] }}</span>
                                            @endif
                                            @if(!empty($attractionData['visit_time']))
                                                <span class="detail-chip">Visit: {{ $attractionData['visit_time'] }}</span>
                                            @endif
                                            @if(!empty($attractionData['transport_note']))
                                                <span class="detail-chip" style="background: #fee2e2; border-color: #fecaca; color: #991b1b;">{{ $attractionData['transport_note'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @php
                                    $restaurantData = isset($card['restaurant']) && is_array($card['restaurant']) ? $card['restaurant'] : [];
                                    $hasRestaurant = count(array_filter($restaurantData)) > 0;
                                @endphp
                                @if($hasRestaurant)
                                    <div class="detail-block">
                                        @if(!empty($restaurantData['meal_type']))
                                            <div class="detail-line">Meal Type: {{ $restaurantData['meal_type'] }}</div>
                                        @endif
                                        @if(!empty($restaurantData['ticket_name']))
                                            <div class="detail-line" style="margin-top:4px;">Ticket: {{ $restaurantData['ticket_name'] }}</div>
                                        @endif
                                        @if(!empty($restaurantData['meal_items']) && is_array($restaurantData['meal_items']) && count($restaurantData['meal_items']) > 0)
                                            <div class="detail-meta" style="margin-top:6px;">
                                                <strong>Menu Items:</strong>
                                                <ul style="margin:4px 0 0 0; padding-left:16px; font-size:11px; color:#075985;">
                                                    @foreach($restaurantData['meal_items'] as $mealItem)
                                                        <li>
                                                            @php
                                                                $itemName = $mealItem['item_name'] ?? null;
                                                                $name = $mealItem['name'] ?? null;
                                                                $displayText = '';
                                                                if ($itemName && $name && $itemName !== $name) {
                                                                    $displayText = $itemName . ' (' . $name . ')';
                                                                } elseif ($itemName) {
                                                                    $displayText = $itemName;
                                                                } elseif ($name) {
                                                                    $displayText = $name;
                                                                }
                                                            @endphp
                                                            @if($displayText)
                                                                {{ $displayText }}
                                                                @if(!empty($mealItem['quantity']) && $mealItem['quantity'] > 1)
                                                                    (x{{ $mealItem['quantity'] }})
                                                                @endif
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <div class="detail-chips" style="margin-top:6px;">
                                            @if(!empty($restaurantData['adult_count']))
                                                <span class="detail-chip">Adults: {{ $restaurantData['adult_count'] }}</span>
                                            @endif
                                            @if(!empty($restaurantData['child_count']))
                                                <span class="detail-chip">Children: {{ $restaurantData['child_count'] }}</span>
                                            @endif
                                            @if(!empty($restaurantData['senior_count']))
                                                <span class="detail-chip">Seniors: {{ $restaurantData['senior_count'] }}</span>
                                            @endif
                                            @if(!empty($restaurantData['visit_time']))
                                                <span class="detail-chip">Visit: {{ $restaurantData['visit_time'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @php
                                    $guideData = isset($card['guide']) && is_array($card['guide']) ? $card['guide'] : [];
                                    $hasGuide = count(array_filter($guideData)) > 0;
                                @endphp
                                @if($hasGuide)
                                    <div class="guide-block">
                                        @if(!empty($guideData['guide_name']))
                                            <div class="guide-line">Guide: {{ $guideData['guide_name'] }}</div>
                                        @endif
                                        <div class="guide-chips">
                                            @if(!empty($guideData['hours']))
                                                <span class="guide-chip">Hours: {{ $guideData['hours'] }}</span>
                                            @endif
                                            @if(!empty($guideData['entry_time']))
                                                <span class="guide-chip">Time: {{ $guideData['entry_time'] }}</span>
                                            @endif
                                            @if(!empty($guideData['languages']) && is_array($guideData['languages']) && count($guideData['languages']) > 0)
                                                <span class="guide-chip">Languages: {{ implode(', ', $guideData['languages']) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($card['hotel_info']['name']))
                                    <div class="hotel-info-block">
                                        <div class="hotel-info-line">
                                            Hotel: {{ $card['hotel_info']['name'] }}
                                        </div>
                                        @if(!empty($card['hotel_info']['location']))
                                            <div class="hotel-info-meta">
                                                Location: {{ $card['hotel_info']['location'] }}
                                            </div>
                                        @endif
                                        <div class="hotel-time-chips">
                                            @if(!empty($card['hotel_info']['check_in_time']))
                                                @php
                                                    try {
                                                        $checkInTime = \Carbon\Carbon::createFromFormat('H:i:s', $card['hotel_info']['check_in_time'])->format('g:i A');
                                                    } catch (\Exception $e) {
                                                        try {
                                                            $checkInTime = \Carbon\Carbon::parse($card['hotel_info']['check_in_time'])->format('g:i A');
                                                        } catch (\Exception $e2) {
                                                            $checkInTime = $card['hotel_info']['check_in_time'];
                                                        }
                                                    }
                                                @endphp
                                                <span class="hotel-time-chip">Check-in: {{ $checkInTime }}</span>
                                            @endif
                                            @if(!empty($card['hotel_info']['check_out_time']))
                                                @php
                                                    try {
                                                        $checkOutTime = \Carbon\Carbon::createFromFormat('H:i:s', $card['hotel_info']['check_out_time'])->format('g:i A');
                                                    } catch (\Exception $e) {
                                                        try {
                                                            $checkOutTime = \Carbon\Carbon::parse($card['hotel_info']['check_out_time'])->format('g:i A');
                                                        } catch (\Exception $e2) {
                                                            $checkOutTime = $card['hotel_info']['check_out_time'];
                                                        }
                                                    }
                                                @endphp
                                                <span class="hotel-time-chip">Check-out: {{ $checkOutTime }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($card['rooms']))
                                    <div class="rooms-block">
                                        @foreach($card['rooms'] as $room)
                                            <div class="room-line">
                                                Room Type: {{ $room['name'] ?? 'Room' }}
                                            </div>
                                            @if(!empty($room['beds']))
                                                <ul class="bed-list">
                                                    @foreach($room['beds'] as $bed)
                                                        <li>
                                                            <strong>{{ $bed['type'] ?? 'Bed' }}</strong>
                                                            @if(!empty($bed['occupancy']))
                                                                • Capacity: {{ $bed['occupancy'] }} pax
                                                            @endif
                                                            @if(!empty($bed['meal']))
                                                                • Meal Plan: {{ $bed['meal'] }}
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($card['notes']))
                                    <div class="notes">{{ $card['notes'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    @if(!empty($tourPrices))
        @php
            // Determine currency symbol - use S$ for Singapore, otherwise use tour currency
            $destination = strtolower($tour->destination ?? '');
            $tourCurrency = $tour->currency ?? '$';
            
            if (stripos($destination, 'singapore') !== false || 
                stripos($tourCurrency, 'sgd') !== false || 
                stripos($tourCurrency, 'singapore') !== false || 
                $tourCurrency === 'S$' || 
                $tourCurrency === 'SGD') {
                $currency = 'S$';
            } elseif ($tourCurrency && $tourCurrency !== '$') {
                $currency = $tourCurrency;
            } else {
                $currency = 'S$'; // Default to Singapore dollar
            }
            
            $singleSharing = $tourPrices['single_sharing'] ?? 0;
            $doubleSharing = $tourPrices['double_sharing'] ?? 0;
            $tripleSharing = $tourPrices['triple_sharing'] ?? 0;
        @endphp
        <div class="price-summary-section">
            <div class="price-summary-header">
                Tour Price Summary
            </div>
            <div class="price-summary-grid">
                <div class="price-summary-item">
                    <div class="price-summary-label">Single Sharing</div>
                    <div class="price-summary-value">
                        <span class="price-summary-currency">{{ $currency }}</span>{{ number_format($singleSharing, 2) }}
                    </div>
                </div>
                <div class="price-summary-item">
                    <div class="price-summary-label">Double Sharing</div>
                    <div class="price-summary-value">
                        <span class="price-summary-currency">{{ $currency }}</span>{{ number_format($doubleSharing, 2) }}
                    </div>
                </div>
                <div class="price-summary-item">
                    <div class="price-summary-label">Triple Sharing</div>
                    <div class="price-summary-value">
                        <span class="price-summary-currency">{{ $currency }}</span>{{ number_format($tripleSharing, 2) }}
                    </div>
                </div>
            </div>
        </div>
    @endif
    </div>
</body>
</html>

