<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome to ForeRent</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
            color: #333333;
        }

        .wrapper {
            width: 100%;
            background-color: #f4f4f4;
            padding: 40px 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            padding: 40px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
        }

        .body {
            padding: 40px;
            line-height: 1.6;
        }

        .body p {
            margin: 0 0 20px;
        }

        .credentials {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-left: 5px solid #111827;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }

        .credentials p {
            margin: 8px 0;
            font-size: 15px;
        }

        .credentials span {
            font-weight: bold;
            color: #111827;
            display: inline-block;
            width: 140px;
        }

        .tip {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 16px;
            font-size: 14px;
            color: #0369a1;
            margin-bottom: 25px;
        }

        .btn-wrapper {
            text-align: center;
            margin: 30px 0;
        }

        .btn {
            display: inline-block;
            background-color: #111827;
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 35px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
        }

        .eyebrow {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }

        .footer {
            text-align: center;
            padding: 25px;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">

            <div class="header">
                <div class="eyebrow">ForeRent</div>
                <h1>Welcome to Your New Account</h1>
            </div>

            <div class="body">
                <p>Hello <strong>{{ $recipientName }}</strong>,</p>

                <p>
                    We are happy to inform you that your registration as a <strong>{{ $accountType }}</strong> is now complete. 
                    You can now use our system to manage your rental details and stay updated.
                </p>

                <p>To get started, please sign in using the information below:</p>

                <div class="credentials">
                    <p><span>Email Address:</span> {{ $email }}</p>
                    <p><span>Temporary Code:</span> {{ $tempPassword }}</p>
                </div>

                <div class="tip">
                    <strong>Important Security Step:</strong> To keep your account safe, the system will ask you 
                    to <strong>create your own personal password</strong> immediately after you sign in for the first time.
                </div>

                <p>Click the button below to visit our website and sign in:</p>

                <div class="btn-wrapper">
                    <a href="https://forerent.onrender.com/" class="btn">Sign In to Your Account</a>
                </div>

                <p>
                    If you have any questions or need help, please feel free to reach out to our support team.
                </p>

                <p>
                    Best regards,<br>
                    <strong>The ForeRent Team</strong>
                </p>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} ForeRent. Sent by University of Makati Thesis Project.
            </div>

        </div>
    </div>
</body>

</html>