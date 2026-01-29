<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $slot }}</title>
</head>

<body style="margin:0; padding:0; background:#f4f7fa; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fa; padding:30px 0;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; padding:0; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.05);">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="background:#1e40af; padding:25px; color:white; font-size:22px; font-weight:bold;">
                            {{ $header ?? 'Hiring Boat Notification' }}
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:30px;">
                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:20px; text-align:center; background:#f1f5f9; color:#64748b; font-size:13px;">
                            <p><strong>Hiring Boat</strong> – Automated Admin Alert</p>
                            <p>&copy; {{ date('Y') }} Hiring Boat. All rights reserved.</p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
