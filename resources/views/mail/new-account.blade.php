<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ForeRent</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        .wrapper {
            width: 100%;
            padding: 40px 0;
        }

        .container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
        }

        /* ✅ HEADER FIXED */
        .header {
            background: linear-gradient(135deg, #1f2937, #374151);
            padding: 40px 30px;
            /* ✅ left/right spacing */
            text-align: center;
        }

        .eyebrow {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-size: 12px;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            width: 100%;
        }

        /* ✅ BODY FIXED (SIDE SPACING) */
        .body {
            padding: 40px 30px;
            /* ✅ consistent spacing */
            color: #333;
            line-height: 1.7;
        }

        .body p {
            margin: 0 0 18px;
        }

        .greeting {
            margin-bottom: 25px;
        }

        .credentials {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-left: 5px solid #111827;
            border-radius: 8px;
            padding: 18px;
            margin: 25px 0;
        }

        .credentials p {
            margin: 6px 0;
        }

        .credentials span {
            font-weight: bold;
            display: inline-block;
            width: 130px;
        }

        .tip {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 18px;
            margin: 25px 0;
            font-size: 14px;
            color: #0369a1;
        }

        .btn-wrapper {
            text-align: center;
            margin: 35px 0;
        }

        .btn {
            background: #111827;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 6px;
            display: inline-block;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 25px 30px;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">

            <!-- HEADER -->
            <div class="header">
                <div class="eyebrow">ForeRent</div>
                <h1>Welcome to Your New Account</h1>
            </div>

            <!-- BODY -->
            <div class="body">
                <p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>

                <p>
                    We are pleased to inform you that your registration as a
                    <strong>{{ $accountType }}</strong> is now complete.
                    You can now use our system to help manage your properties.
                </p>

                <p>To get started, please sign in using the details below:</p>

                <div class="credentials">
                    <p><span>Your Email:</span> {{ $email }}</p>
                    <p><span>Temporary Code:</span> {{ $tempPassword }}</p>
                </div>

                <div class="tip">
                    <strong>Important:</strong> You will be asked to create your own password after login.
                </div>

                <p>Click below to sign in:</p>

                <div class="btn-wrapper">
                    <a href="https://forerent.onrender.com/" class="btn">
                        Sign In
                    </a>
                </div>

                <p>If you need help, feel free to contact support.</p>

                <p>
                    Best regards,<br>
                    <strong>The ForeRent Team</strong>
                </p>
            </div>

            <!-- FOOTER -->
            <div class="footer">
                &copy; {{ date('Y') }} ForeRent<br>
                University of Makati Thesis Project
            </div>

        </div>
    </div>
</body>

</html>
