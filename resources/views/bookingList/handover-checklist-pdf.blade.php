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
        .header {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        .header-left {
            display: table-cell;
            width: 65%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 35%;
            text-align: right;
            vertical-align: top;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .company-details {
            font-size: 9px;
            color: #444;
            line-height: 1.4;
        }
        .logo-img {
            max-height: 80px;
            max-width: 120px;
            display: inline-block;
        }
        .title-row {
            text-align: center;
            margin: 10px 0 8px;
        }
        .title-main {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
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
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 10px 0 4px;
            padding: 4px 6px;
            border: 1px solid #000;
            border-bottom: none;
        }
        .tickets-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
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
        }
        .tickets-table td.center {
            text-align: center;
        }
        .tickets-table td.right {
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            <div class="company-name">
                {{ $dmc->company_name ?? $dmc->name ?? config('app.name') }}
            </div>
            <div class="company-details">
                @if(!empty($dmc->address))
                    {{ $dmc->address }}<br>
                @endif
                @if(!empty($dmc->phone) || !empty($dmc->tel))
                    Tel: {{ $dmc->phone ?? $dmc->tel }}
                    @if(!empty($dmc->fax))
                        &nbsp;|&nbsp; Fax: {{ $dmc->fax }}
                    @endif
                    <br>
                @endif
                @if(!empty($dmc->email))
                    Email: {{ $dmc->email }}<br>
                @endif
                @if(!empty($dmc->website))
                    Website: {{ $dmc->website }}
                @endif
            </div>
        </div>
        <div class="header-right">
            @if(!empty($dmc->logo))
                <img src="{{ $dmc->logo }}" alt="Logo" class="logo-img">
            @endif
        </div>
    </div>

    <div class="title-row">
        <div class="title-main">HANDOVER ACKNOWLEDGEMENT CHECKLIST</div>
    </div>

    <table class="summary-table">
        <tr>
            <td class="summary-label" style="width: 14%;">Destination :</td>
            <td style="width: 36%;">{{ $destination ?? ($tourDetails->destination ?? '') }}</td>
            <td class="summary-label" style="width: 16%;">Sales Ref No :</td>
            <td style="width: 34%;">{{ $display_id ?? $tourId ?? '' }}</td>
        </tr>
        <tr>
            <td class="summary-label">Guest Name :</td>
            <td>{{ $guest_name ?? ($tourDetails->guest_name ?? '') }}</td>
            <td class="summary-label">Date Of Arrival :</td>
            <td>
                @if(!empty($arrival_date))
                    {{ \Carbon\Carbon::parse($arrival_date)->format('d M Y') }}
                @elseif(!empty($tourDetails->check_in_time))
                    {{ \Carbon\Carbon::parse($tourDetails->check_in_time)->format('d M Y') }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="summary-label">Adults :</td>
            <td>{{ $adults ?? ($tourDetails->adult ?? 0) }}</td>
            <td class="summary-label">Nationality :</td>
            <td>{{ $nationality ?? '' }}</td>
        </tr>
        <tr>
            <td class="summary-label">CWB :</td>
            <td>{{ $cwb ?? 0 }}</td>
            <td class="summary-label">CNB :</td>
            <td>{{ $cnb ?? 0 }}</td>
        </tr>
    </table>

    <div class="section-title">Ticket Coupons</div>

    <table class="tickets-table">
        <thead>
            <tr>
                <th style="width: 18%;">Service Date</th>
                <th style="width: 18%;">Service Type</th>
                <th>Service</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ticketCoupons ?? [] as $row)
                <tr>
                    <td class="center">
                        @if(!empty($row['service_date']))
                            {{ \Carbon\Carbon::parse($row['service_date'])->format('d M, Y') }}
                        @endif
                    </td>
                    <td class="center">
                        {{ $row['service_type'] ?? '' }}
                    </td>
                    <td class="{{ !empty($row['is_subrow']) ? 'service-subrow' : '' }}">
                        {!! $row['service'] ?? '' !!}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="center">No ticket coupons available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

