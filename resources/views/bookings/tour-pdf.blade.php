<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tour Details - {{ $tour->display_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #007bff;
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #007bff;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 6px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-table td:first-child {
            font-weight: bold;
            width: 40%;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-new { background-color: #fff3cd; color: #856404; }
        .status-confirmed { background-color: #d1ecf1; color: #0c5460; }
        .status-definite { background-color: #d4edda; color: #155724; }
        .status-actual { background-color: #d1edfd; color: #084298; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        .id-badge {
            background-color: #007bff;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        
        .payment-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        
        .payment-item {
            margin-bottom: 10px;
            padding: 8px;
            background-color: white;
            border-radius: 3px;
            border-left: 3px solid #28a745;
        }
        
        @media print {
            body { padding: 10px; }
            .section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Tour Details</h1>
        <p>Generated on {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
    </div>

    <!-- Tour Overview -->
    <div class="section">
        <div class="section-title">Tour Overview</div>
        <div class="info-grid">
            <div>
                <table class="info-table">
                    <tr>
                        <td>Display ID:</td>
                        <td><span class="id-badge">{{ $tour->display_id }}</span></td>
                    </tr>
                    <tr>
                        <td>Tour ID:</td>
                        <td><span class="id-badge">{{ $tour->tour_id }}</span></td>
                    </tr>
                    @if($tour->multi_enq_id)
                    <tr>
                        <td>Multi Enquiry ID:</td>
                        <td><span class="id-badge">{{ $tour->multi_enq_id }}</span></td>
                    </tr>
                    @endif
                    <tr>
                        <td>Status:</td>
                        <td>
                            <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $tour->tour_status)) }}">
                                {{ $tour->tour_status }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Created:</td>
                        <td>{{ \Carbon\Carbon::parse($tour->created_at)->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td>Updated:</td>
                        <td>{{ \Carbon\Carbon::parse($tour->updated_at)->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
            <div>
                <table class="info-table">
                    <tr>
                        <td>Destination:</td>
                        <td>{{ $tour->destination ?? 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td>City:</td>
                        <td>{{ $tour->city ?? 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td>Check-in:</td>
                        <td>{{ $tour->check_in_time ? \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y h:i A') : 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td>Check-out:</td>
                        <td>{{ $tour->check_out_time ? \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y h:i A') : 'Not specified' }}</td>
                    </tr>
                    @if($tour->agent_name)
                    <tr>
                        <td>Agent:</td>
                        <td>{{ $tour->agent_name }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Guest Information -->
    <div class="section">
        <div class="section-title">Guest Information</div>
        <table class="info-table">
            <tr>
                <td>Adults:</td>
                <td>{{ $tour->adult ?? 0 }}</td>
            </tr>
            <tr>
                <td>Children:</td>
                <td>{{ $tour->child ?? 0 }}</td>
            </tr>
            <tr>
                <td>Total Guests:</td>
                <td>{{ ($tour->adult ?? 0) + ($tour->child ?? 0) }}</td>
            </tr>
        </table>
    </div>

    <!-- Payment Information -->
    @if(!empty($tour->parsed_payment_details) && count($tour->parsed_payment_details) > 0)
    <div class="section">
        <div class="section-title">Payment Information</div>
        <div class="payment-section">
            @foreach($tour->parsed_payment_details as $payment)
            <div class="payment-item">
                <table class="info-table">
                    <tr>
                        <td>Payment Date:</td>
                        <td>{{ isset($payment['payment_date']) ? \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') : 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td>Amount:</td>
                        <td>{{ $payment['amount'] ?? 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td>Method:</td>
                        <td>{{ $payment['payment_type'] ?? 'Not specified' }}</td>
                    </tr>
                    @if(isset($payment['reference']))
                    <tr>
                        <td>Reference:</td>
                        <td>{{ $payment['reference'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Additional Details -->
    @if($tour->notes || $tour->special_requests)
    <div class="section">
        <div class="section-title">Additional Information</div>
        <table class="info-table">
            @if($tour->notes)
            <tr>
                <td>Notes:</td>
                <td>{{ $tour->notes }}</td>
            </tr>
            @endif
            @if($tour->special_requests)
            <tr>
                <td>Special Requests:</td>
                <td>{{ $tour->special_requests }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This document was generated automatically from the tour management system.</p>
        <p>Document ID: {{ $tour->display_id }} | Generated: {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
