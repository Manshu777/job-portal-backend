<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Job Posting Awaiting Activation</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #007bff; border-radius: 8px 8px 0 0; padding: 20px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">New Job Posting Awaiting Activation</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                                A new job posting has been submitted and requires your approval to be activated.
                            </p>
                            <h2 style="color: #333333; font-size: 18px; margin: 20px 0 10px;">Job Details</h2>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="color: #333333; font-size: 14px; padding: 8px 0; font-weight: bold; width: 30%;">Title:</td>
                                    <td style="color: #333333; font-size: 14px; padding: 8px 0;">{{ $jobTitle }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #333333; font-size: 14px; padding: 8px 0; font-weight: bold;">Employer ID:</td>
                                    <td style="color: #333333; font-size: 14px; padding: 8px 0;">{{ $employerId }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #333333; font-size: 14px; padding: 8px 0; font-weight: bold;">Job Type:</td>
                                    <td style="color: #333333; font-size: 14px; padding: 8px 0;">{{ $jobType }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #333333; font-size: 14px; padding: 8px 0; font-weight: bold;">Location:</td>
                                    <td style="color: #333333; font-size: 14px; padding: 8px 0;">{{ $location }}</td>
                                </tr>
                            </table>
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 20px 0;">
                                Please review the job posting and activate it using the button below:
                            </p>
                            <!-- <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{{ $activationLink }}" style="background-color: #007bff; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 5px; font-size: 16px; font-weight: bold; display: inline-block;">Activate Job Posting</a>
                                    </td>
                                </tr>
                            </table> -->
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; border-radius: 0 0 8px 8px; padding: 20px; text-align: center;">
                            <p style="color: #666666; font-size: 14px; margin: 0;">
                                Thank you,<br>
                                 Team
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>