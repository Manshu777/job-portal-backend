<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Company Registered</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); overflow: hidden;">
        <tr>
            <td style="background-color: #007bff; color: #ffffff; padding: 20px; text-align: center;">
                <h2 style="margin: 0;">🎉 New Company Registered!</h2>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; margin-bottom: 10px;"><strong>📛 Company Name:</strong> {{ $companyName }}</p>
                <p style="font-size: 16px; margin-bottom: 10px;"><strong>👤 Contact Person:</strong> {{ $contactPerson }}</p>
                <p style="font-size: 16px; margin-bottom: 10px;"><strong>📧 Email:</strong> <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></p>
                <p style="font-size: 16px; margin-bottom: 10px;"><strong>📍 Location:</strong> {{ $location }}</p>
                <p style="font-size: 16px; margin-bottom: 20px;"><strong>📞 Phone:</strong> {{ $phone }}</p>

                <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">

                <p style="font-size: 14px; color: #666;">You have received this message because a new employer has signed up on your platform.</p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f1f1f1; padding: 20px; text-align: center; font-size: 13px; color: #999;">
                &copy; {{ date('Y') }} YourCompany. All rights reserved.
            </td>
        </tr>
    </table>
</body>
</html>
