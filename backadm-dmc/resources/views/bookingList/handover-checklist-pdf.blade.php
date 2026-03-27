<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Handover Checklist - {{ $display_id ?? $tourId ?? '' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 18px 18px 12px 18px;
        }
        @include('invoices.pdf.partials.header-css')
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }
        .summary-table td {
            padding: 3px 4px;
            vertical-align: top;
        }
        .summary-label {
            font-weight: bold;
            color: #f97316;
        }
        .summary-value {
            color: #f97316;
            font-weight: 600;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0 4px;
            padding: 4px 6px;
            border: 1px solid #000;
            border-bottom: none;
        }
        .tickets-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .tickets-table th,
        .tickets-table td {
            border: 1px solid #000;
            padding: 4px 5px;
        }
        .tickets-table th {
            background-color: #f3f3f3;
            font-weight: bold;
            text-align: left;
        }
        .tickets-table td.service-subrow {
            font-style: italic;
            color: #666;
            padding-left: 10px;
            background: #fafafa;
        }
        .tickets-table td.center {
            text-align: center;
        }
        .tickets-table td.right {
            text-align: right;
        }
        .divider-line {
            border-top: 1px solid #000;
            margin: 6px 0 10px;
        }
        .approval {
            font-weight: 700;
        }
        .approval.approved {
            color: #16a34a; /* green */
        }
        .approval.pending {
            color: #dc2626; /* red */
        }
    </style>
</head>
<body>
    @include('invoices.pdf.partials.header', [
        'logoType' => 'dmc',
        'showBlueTitle' => true,
        'docTitle' => 'HANDOVER ACKNOWLEDGEMENT CHECKLIST',
        'docNumber' => ($display_id ?? $tourId ?? ''),
        'user_dmc' => $dmc,
    ])

    <div class="divider-line"></div>

    <table class="summary-table">
        <tr>
            <td class="summary-label" style="width: 14%;">Destination :</td>
            <td class="summary-value" style="width: 36%;">{{ $destination ?? ($tourDetails->destination ?? '') }}</td>
            <td class="summary-label" style="width: 16%;">Sales Ref No :</td>
            <td class="summary-value" style="width: 34%;">{{ $display_id ?? $tourId ?? '' }}</td>
        </tr>
        <tr>
            <td class="summary-label">Guest Name :</td>
            <td class="summary-value">{{ $guest_name ?? ($tourDetails->guest_name ?? '') }}</td>
            <td class="summary-label">Date Of Arrival :</td>
            <td class="summary-value">
                @if(!empty($arrival_date))
                    {{ \Carbon\Carbon::parse($arrival_date)->format('d M Y') }}
                @elseif(!empty($tourDetails->check_in_time))
                    {{ \Carbon\Carbon::parse($tourDetails->check_in_time)->format('d M Y') }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="summary-label">Adults :</td>
            <td class="summary-value">{{ $adults ?? ($tourDetails->adult ?? 0) }}</td>
            <td class="summary-label">Nationality :</td>
            <td class="summary-value">{{ $nationality ?? '' }}</td>
        </tr>
        <tr>
            <td class="summary-label">CWB :</td>
            <td class="summary-value">{{ $cwb ?? 0 }}</td>
            <td class="summary-label">CNB :</td>
            <td class="summary-value">{{ $cnb ?? 0 }}</td>
        </tr>
    </table>


    <table class="tickets-table">
        <thead>
            <tr>
                <th style="width: 18%;">Service Date</th>
                <th style="width: 18%;">Service Type</th>
                <th>Service</th>
                <th style="width: 14%;">Approval</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ticketCoupons ?? [] as $row)
                <tr>
                    <td class="center">
                        @if(empty($row['is_subrow']) && !empty($row['service_date']))
                            {{ \Carbon\Carbon::parse($row['service_date'])->format('d M, Y') }}
                        @endif
                    </td>
                    <td class="center">
                        @if(empty($row['is_subrow']))
                            {{ $row['service_type'] ?? '' }}
                        @endif
                    </td>
                    <td class="{{ !empty($row['is_subrow']) ? 'service-subrow' : '' }}">
                        {!! $row['service'] ?? '' !!}
                    </td>
                    <td class="center">
                        @if(empty($row['is_subrow']))
                            @if(!empty($row['is_approve']))
                                <span class="approval approved">&#10003; Approved</span>
                            @else
                                <span class="approval pending">Pending</span>
                            @endif
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center">No ticket coupons available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

