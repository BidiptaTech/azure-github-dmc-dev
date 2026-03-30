<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Itinerary - {{ $display_id ?? $tourId }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 12px; }
        @include('invoices.pdf.partials.header-css')
        .band { background-color: #e5e7eb; padding: 6px 10px; margin-bottom: 10px; font-weight: bold; }
        .band span { margin-right: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .hotel-table th, .hotel-table td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        .hotel-table th { background-color: #e5e7eb; font-weight: bold; font-size: 12px; }
        .day-table th { font-size: 11px; color: #666; text-align: left; padding: 4px 0; border-bottom: 1px dashed #999; }
        .day-table th.col-type { text-align: right; }
        .day-heading { font-size: 14px; font-weight: bold; color: #2563eb; margin: 14px 0 6px; padding-bottom: 2px; }
        .day-row { vertical-align: top; }
        .day-row .col-time { width: 50px; padding: 4px 8px 4px 0; vertical-align: top; }
        .day-row .col-desc { padding: 4px 0; line-height: 1.35; }
        .day-row .col-type { width: 70px; text-align: right; padding: 4px 0 4px 8px; vertical-align: top; }
        .loc { font-weight: bold; color: #dc2626; text-decoration: underline; }
        .note { margin-left: 12px; margin-top: 2px; font-size: 11px; color: #333; }
        .note-green { color: #059669; }
        .activity { margin-left: 12px; margin-top: 2px; font-size: 11px; }
        .dashed { border: none; border-top: 1px dashed #999; margin: 10px 0; }
        .pdf-section { margin-top: 14px; page-break-inside: avoid; }
        .pdf-section-heading { font-size: 13px; font-weight: bold; color: #1e293b; margin-bottom: 6px; padding: 4px 0; border-bottom: 1px solid #cbd5e1; }
        .pdf-section-body { font-size: 11px; line-height: 1.45; color: #334155; white-space: pre-line; }
        .pdf-rich { white-space: normal; }
        .pdf-rich p { margin: 0 0 6px 0; }
        .pdf-rich br { line-height: 1.6; }
        .pdf-rich b, .pdf-rich strong { font-weight: bold; }
        .pdf-rich ul, .pdf-rich ol { margin: 0 0 6px 16px; padding: 0; }
        .pdf-rich li { margin: 0 0 4px 0; }
        .hotel-block .hotel-table { margin-bottom: 1px; }
        .hotel-remarks.pdf-section-body { margin-top: -15px; }
    </style>
</head>
<body>

    @include('invoices.pdf.partials.header', [
        'logoType' => 'dmc',
        'showBlueTitle' => true,
        'docTitle' => 'ITINERARY',
        'docNumber' => ($display_id ?? $tourId),
    ])

    <div class="band">
        <span>Group / Pax: {{ $agent_info['company_name'] ?? 'N/A' }}</span>
        <span>Adults: {{ $adults ?? 0 }}</span>
    </div>

    @if(!empty($pdfHotels))
        <div class="hotel-block">
            <table class="hotel-table">
                <thead>
                    <tr>
                        <th>Hotel</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Nights</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pdfHotels as $h)
                        <tr>
                            <td>{{ $h['name'] }}</td>
                            <td>{{ $h['check_in'] ? \Carbon\Carbon::parse($h['check_in'])->format('d-M-Y') : '—' }}</td>
                            <td>{{ $h['check_out'] ? \Carbon\Carbon::parse($h['check_out'])->format('d-M-Y') : '—' }}</td>
                            <td>{{ $h['nights'] ?? 1 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @php
                $hotelsWithRemarks = array_filter($pdfHotels, function ($h) { return !empty($h['remarks'] ?? ''); });
            @endphp
            @if(count($hotelsWithRemarks) > 0)
                <div class="pdf-section-body hotel-remarks">
                    @foreach($hotelsWithRemarks as $h)
                        <p style="margin: 0 0 1px 0;"><strong>{{ $h['name'] }}</strong>: {{ $h['remarks'] }}</p>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <hr class="dashed">

    <table class="day-table">
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Description</th>
                <th class="col-type">Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pdfDays as $dateStr => $day)
                <tr>
                    <td colspan="3" class="day-heading">{{ $day['date_label'] }}</td>
                </tr>
                @foreach($day['rows'] as $row)
                    <tr class="day-row">
                        <td class="col-time">{{ $row['time'] ?? '—' }}</td>
                        <td class="col-desc">
                            {!! $row['description'] ?? '—' !!}
                            @if(!empty($row['activity']))
                                <div class="activity">Activity: {{ $row['activity'] }}</div>
                            @endif
                            @if(!empty($row['note']))
                                <div class="note {{ !empty($row['note_green']) ? 'note-green' : '' }}">Note: {{ $row['note'] }}</div>
                            @endif
                        </td>
                        <td class="col-type">{{ $row['type'] ?? 'Private' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    @if(!empty($emergency_contact))
        <div class="pdf-section">
            <div class="pdf-section-heading">EMERGENCY CONTACT NOS:</div>
            <div class="pdf-section-body">{!! nl2br(e($emergency_contact)) !!}</div>
        </div>
    @endif

    @if(!empty($terms_and_conditions))
        <div class="pdf-section">
            <div class="pdf-section-heading">Terms &amp; Conditions:</div>
            <div class="pdf-section-body">{!! nl2br(e($terms_and_conditions)) !!}</div>
        </div>
    @endif

    @if(!empty($sic_timing))
        <div class="pdf-section">
            <div class="pdf-section-heading">SIC TOUR PICK UP/DROP TIMING:</div>
            <div class="pdf-section-body">{!! nl2br(e($sic_timing)) !!}</div>
        </div>
    @endif

    @if(!empty($meeting_points))
        <div class="pdf-section">
            <div class="pdf-section-heading">MEETING POINTS:</div>
            <div class="pdf-section-body">{!! nl2br(e($meeting_points)) !!}</div>
        </div>
    @endif

    @if(!empty($itinerary_information))
        <div class="pdf-section">
            
            <div class="pdf-section-body pdf-rich">{!! $itinerary_information !!}</div>
        </div>
    @endif

</body>
</html>
