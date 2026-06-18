@php
    use App\Models\Setting;

    $masterLogo = Setting::where('name', 'logo')->first();
    $masterName = Setting::where('name', 'name')->first();
    $settingLogo = $masterLogo ? $masterLogo->value : '';
    $settingName = $masterName ? $masterName->value : (config('app.name') ?: 'Travclicks');

    $logo        = !empty($dmc_logo) ? $dmc_logo : $settingLogo;
    $companyName = !empty($dmc_label) ? $dmc_label : (!empty($dmc_name) ? $dmc_name : $settingName);
    $tagline     = $tagline ?? 'Travel Designed Around You';
    $recipientName = !empty($recipient_name) ? $recipient_name : 'Valued Customer';

    $supportEmail = !empty($dmc_contact_email) ? $dmc_contact_email : ($supportEmail ?? 'reservations.travclicks@gmail.com');
    $supportPhone = $supportPhone ?? '+65 6201 2366';

    $brandBlue = '#2563eb';
    $textDark  = '#1f2a44';
    $textMuted = '#6b7280';
    $border    = '#e9edf5';
    $bgSoft    = '#f5f7fb';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Additional Information Required</title>
</head>
<body style="margin:0; padding:0; background-color:{{ $bgSoft }}; font-family:'Segoe UI', Arial, Helvetica, sans-serif; color:{{ $textDark }}; -webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:{{ $bgSoft }}; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="680" cellpadding="0" cellspacing="0" style="width:680px; max-width:680px; background-color:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 8px 30px rgba(31,42,68,0.08);">

                    <tr>
                        <td style="padding:22px 28px; border-bottom:1px solid {{ $border }};">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle;" width="55%">
                                        @if($logo)
                                            <img src="{{ $logo }}" alt="{{ $companyName }}" style="max-height:34px; display:block;">
                                        @else
                                            <span style="font-size:22px; font-weight:700; color:{{ $brandBlue }};">{{ $companyName }}</span>
                                        @endif
                                        <div style="font-size:11px; color:{{ $textMuted }}; margin-top:4px;">{{ $tagline }}</div>
                                    </td>
                                    <td style="vertical-align:middle; text-align:right;" width="45%">
                                        <div style="font-size:11px; color:{{ $textMuted }};">Need help?</div>
                                        <a href="mailto:{{ $supportEmail }}" style="font-size:13px; color:{{ $brandBlue }}; text-decoration:none; font-weight:600;">{{ $supportEmail }}</a>
                                        @if(!empty($supportPhone))
                                            <div style="font-size:12px; color:{{ $textDark }}; margin-top:2px;">{{ $supportPhone }}</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 28px;">
                            <p style="font-size:18px; font-weight:600; margin:0 0 20px 0; color:{{ $textDark }};">
                                Dear {{ $recipientName }},
                            </p>

                            <p style="font-size:15px; line-height:1.7; color:{{ $textDark }}; margin:0 0 20px 0;">
                                Thank you for your inquiry. Unfortunately, we are unable to process your request because we did not receive complete travel details. Please provide the following information so that we can proceed:
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px 0; background-color:#f8fafc; border:1px solid {{ $border }}; border-radius:10px;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <ul style="margin:0; padding-left:20px; font-size:15px; line-height:1.9; color:{{ $textDark }};">
                                            <li>Destination country</li>
                                            <li>Number of nights/days of stay</li>
                                            <li>Travel dates</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:15px; line-height:1.7; color:{{ $textDark }}; margin:0 0 28px 0;">
                                Once we receive the required information, we will process your request and get back to you as soon as possible.
                            </p>

                            <p style="font-size:15px; line-height:1.7; color:{{ $textDark }}; margin:0;">
                                Warm regards,<br>
                                <strong>{{ $companyName }} Team</strong>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px; background-color:#f8fafc; border-top:1px solid {{ $border }}; text-align:center; font-size:12px; color:{{ $textMuted }};">
                            &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
