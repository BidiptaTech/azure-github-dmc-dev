<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tour MIS Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1 { font-size: 14px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        tr:nth-child(even) { background-color: #fafafa; }
        .text-right { text-align: right; }
        .meta { color: #666; margin-bottom: 8px; font-size: 8px; }
    </style>
</head>
<body>
    <h1>Tour MIS Report</h1>
    <p class="meta">Generated on {{ now()->format('d M Y H:i') }}@if(!empty(array_filter($filters ?? []))) &nbsp;| Filters: From {{ $filters['from_date'] ?? '—' }} To {{ $filters['to_date'] ?? '—' }}@endif</p>
    <table>
        <thead>
            <tr>
                <th>Booking Date</th>
                <th>Type</th>
                <th>Tour Id</th>
                <th>Destination</th>
                <th>Agent Name</th>
                <th>DMC Name</th>
                <th>PAX</th>
                <th class="text-right">Selling Price</th>
                <th class="text-right">Net Profit</th>
                <th>Transaction Ref.</th>
                <th>Payment Status</th>
                <th>Booking Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report as $row)
                <tr>
                    <td>{{ $row->booking_date ? \Carbon\Carbon::parse($row->booking_date)->format('d M Y') : '—' }}</td>
                    <td>{{ $row->type ?? '—' }}</td>
                    <td>{{ $row->tour_name ?? '—' }}</td>
                    <td>{{ $row->destination }}</td>
                    <td>{{ $row->agent_name }}</td>
                    <td>{{ $row->dmc_name }}</td>
                    <td>{{ $row->pax }}</td>
                    <td class="text-right">{{ number_format($row->selling_price, 2) }}</td>
                    <td class="text-right">{{ number_format($row->net_profit, 2) }}</td>
                    <td>{{ $row->transaction_reference_number }}</td>
                    <td>{{ $row->payment_status ?: '—' }}</td>
                    <td>{{ $row->booking_status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
