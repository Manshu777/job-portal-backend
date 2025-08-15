<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP</title>
    <style>
        /* General email styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7fafc;
            color: #4a5568;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 36px;
            color: #3182ce;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 18px;
            color: #718096;
        }

        /* OTP Section */
        .otp {
            text-align: center;
            margin-bottom: 20px;
        }

        .otp h2 {
            font-size: 48px;
            color: #48bb78;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .otp p {
            font-size: 16px;
            color: #4a5568;
        }

        /* Thank You Section */
        .thank-you {
            text-align: center;
            margin-top: 30px;
        }

        .thank-you p {
            font-size: 18px;
            color: #4a5568;
            margin-bottom: 10px;
        }

        .thank-you small {
            font-size: 14px;
            color: #e2e8f0;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 12px;
            color: #e2e8f0;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <h1>Job Portal</h1>
            <p>Your OTP for login</p>
        </div>

        <!-- OTP Section -->
        <div class="otp">
            <h2>{{ $otp }}</h2>
            <p>This OTP will expire in 10 minutes. Please do not share it with anyone.</p>
        </div>

        <!-- Thank You Section -->
        <div class="thank-you">
            <p>Thank you for using our Job Portal! We wish you the best in your job search.</p>
            <small>If you did not request this OTP, please ignore this message.</small>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; 2025 Job Portal. All Rights Reserved.</p>
        </div>
    </div>
</body>

</html>
