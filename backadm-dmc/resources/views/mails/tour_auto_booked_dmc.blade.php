@php
    $guestParts = [];
    if (($adults ?? 0) > 0) {
        $guestParts[] = $adults . ' adult' . ($adults > 1 ? 's' : '');
    }
    if (($children ?? 0) > 0) {
        $guestParts[] = $children . ' child' . ($children > 1 ? 'ren' : '');
    }
    if (($infants ?? 0) > 0) {
        $guestParts[] = $infants . ' infant' . ($infants > 1 ? 's' : '');
    }
    $guestsText = count($guestParts) ? implode(', ', $guestParts) : (($total_guests ?? 0) . ' guest(s)');
@endphp
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px 0; color: #333; line-height: 1.6;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 680px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
        <tr>
            <td style="background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%); padding: 36px 30px; text-align: center; color: white;">
                <div style="margin-bottom: 20px;">
                    @if(!empty($dmc_logo))
                    <img src="{{ $dmc_logo }}" alt="{{ $dmc_label ?? ($dmc_name ?? 'DMC') }}" style="max-width: 120px; height: auto; border-radius: 8px; border: 3px solid rgba(255,255,255,0.9); background: white; padding: 8px;">
                    @elseif(!empty($dmc_label))
                    <div style="display: inline-block; background: white; color: #7c3aed; font-weight: 700; font-size: 16px; padding: 12px 20px; border-radius: 8px; border: 3px solid rgba(255,255,255,0.9);">
                        {{ $dmc_label }}
                    </div>
                    @endif
                </div>
                <h1 style="margin: 0; font-size: 24px; font-weight: 700; color: white;">
                    Tour booking — {{ $tour_display_id ?? '' }}
                </h1>
            </td>
        </tr>

        <tr>
            <td style="padding: 32px 35px;">
                <p style="font-size: 16px; margin: 0 0 12px 0; color: #1e293b;">
                    Hi {{ $dmc_name ?? 'there' }},
                </p>

                @if(!empty($is_partial_package) && !empty($partial_package_message))
                <div style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 12px; padding: 16px 18px; margin: 0 0 20px 0;">
                    <p style="margin: 0; font-size: 15px; color: #92400e; line-height: 1.6;">
                        {{ $partial_package_message }}
                    </p>
                </div>
                @else
                <p style="font-size: 15px; color: #475569; margin: 0 0 20px 0; line-height: 1.7;">
                    A tour was booked from the available package. Summary is below.
                </p>
                @endif

                <div style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border-radius: 16px; padding: 24px 26px; margin-bottom: 24px; border: 2px solid #c4b5fd;">
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0 0 16px 0; color: #7c3aed;">
                        Booking details
                    </h3>

                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td style="padding: 6px 0; font-size: 14px; color: #64748b; width: 38%;">Tour</td>
                            <td style="padding: 6px 0; font-size: 15px; color: #1e293b; font-weight: 600;">{{ $tour_display_id ?? 'N/A' }}</td>
                        </tr>
                        @if(!empty($dmc_label))
                        <tr>
                            <td style="padding: 6px 0; font-size: 14px; color: #64748b;">DMC</td>
                            <td style="padding: 6px 0; font-size: 15px; color: #1e293b;">{{ $dmc_label }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 6px 0; font-size: 14px; color: #64748b;">Destination</td>
                            <td style="padding: 6px 0; font-size: 15px; color: #1e293b;">
                                {{ $country ?? ($destination ?? 'N/A') }}
                                @if(!empty($cities_label))
                                — {{ $cities_label }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; font-size: 14px; color: #64748b;">Dates</td>
                            <td style="padding: 6px 0; font-size: 15px; color: #1e293b;">{{ $check_in_date ?? 'N/A' }} to {{ $check_out_date ?? 'N/A' }}</td>
                        </tr>
                        @if(!empty($requested_days))
                        <tr>
                            <td style="padding: 6px 0; font-size: 14px; color: #64748b;">Requested</td>
                            <td style="padding: 6px 0; font-size: 15px; color: #1e293b;">{{ $requested_days }} day{{ $requested_days > 1 ? 's' : '' }}</td>
                        </tr>
                        @endif
                        @if(!empty($available_days))
                        <tr>
                            <td style="padding: 6px 0; font-size: 14px; color: #64748b;">Package available</td>
                            <td style="padding: 6px 0; font-size: 15px; color: #1e293b;">{{ $available_days }} day{{ $available_days > 1 ? 's' : '' }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 6px 0; font-size: 14px; color: #64748b;">Guests</td>
                            <td style="padding: 6px 0; font-size: 15px; color: #1e293b;">{{ $guestsText }}</td>
                        </tr>
                        @if(!empty($agent_name))
                        <tr>
                            <td style="padding: 6px 0; font-size: 14px; color: #64748b;">Agent</td>
                            <td style="padding: 6px 0; font-size: 15px; color: #1e293b;">
                                {{ $agent_name }}
                                @if(!empty($agency_name))
                                ({{ $agency_name }})
                                @endif
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 6px 0; font-size: 14px; color: #64748b;">Booked</td>
                            <td style="padding: 6px 0; font-size: 15px; color: #1e293b;">{{ $booked_at ?? now()->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>

                @if(!empty($booked_services) && count($booked_services) > 0)
                <div style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border-radius: 16px; padding: 24px 26px; margin-bottom: 24px; border: 2px solid #c4b5fd;">
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0 0 12px 0; color: #7c3aed;">
                        Services booked
                    </h3>
                    <ul style="margin: 0; padding-left: 20px; font-size: 15px; color: #1e293b; line-height: 1.8;">
                        @foreach($booked_services as $service)
                        <li>
                            {{ $service['type'] ?? 'Service' }}: {{ $service['name'] ?? '—' }}
                            @if(!empty($service['date']))
                            , {{ $service['date'] }}
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <p style="margin: 0 0 20px 0; text-align: center;">
                    <a href="{{ $dashboard_link ?? route('dashboard') }}" style="display: inline-block; background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%); color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 10px; font-weight: 600; font-size: 15px;">
                        Dashboard
                    </a>
                </p>

                @if(!empty($dmc_contact_email))
                <p style="margin: 0; font-size: 14px; color: #64748b;">
                    For help, email <a href="mailto:{{ $dmc_contact_email }}" style="color: #7c3aed;">{{ $dmc_contact_email }}</a>.
                </p>
                @endif
            </td>
        </tr>
    </table>
</div>
