<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px; margin: 0;">
    <table align="center" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        
        <!-- Header with logo -->
        <tr>
            @php
                use App\Models\Setting;

                $master_logo = Setting::where('name', 'logo')->first();
                $master_name = Setting::where('name', 'name')->first();

                $logo = $master_logo ? $master_logo->value : '';
                $companyName = $master_name ? $master_name->value : config('app.name');
            @endphp
            <td style="background-color: #0d6efd; padding: 30px; text-align: center;">
                @if($logo)
                    <img src="{{ $logo }}" alt="Company Logo" style="max-width: 150px; margin-bottom: 10px;">
                @endif

                <h2 style="color: #ffffff; margin: 0;">{{ $companyName }}</h2>
            </td>
        </tr>


        <!-- Email Body -->
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 18px; margin-bottom: 20px;">Hi {{ $name }},</p>

                <p style="font-size: 16px; line-height: 1.6; color: #333333;">
                    🎉 <strong>Great news!</strong> Your booking has been <strong>successfully confirmed</strong> with <strong>{{ config('app.name') }}</strong>.
                </p>

                <p style="font-size: 16px; line-height: 1.6; color: #333333;">
                    We’re excited to have you with us. You’ll receive further details shortly. Until then, feel free to explore our website or reach out to support if you have any questions.
                </p>

                <p style="font-size: 16px; line-height: 1.6; margin-top: 20px; color: #333333;">
                    📅 Booking Date: <strong>{{ $booking_date ?? 'N/A' }}</strong><br>
                    🏨 Attraction/Hotel: <strong>{{ $booking_item ?? 'N/A' }}</strong>
                </p>

                <p style="margin-top: 30px; font-size: 16px; color: #333333;">
                    Thanks again for choosing us!
                </p>

                <p style="margin-top: 40px; font-size: 16px; color: #333333;">
                    Best regards,<br>
                    <strong>{{ config('app.name') }} Team</strong>
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #888888;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
