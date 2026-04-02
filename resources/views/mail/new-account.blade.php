<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body {
            margin: 0; padding: 0;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            font-size: 15px;
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
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .header {
            background-color: #2d3748;
            padding: 32px 40px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .body {
            padding: 36px 40px;
            line-height: 1.7;
        }
        .body p {
            margin: 0 0 16px;
        }
        .credentials {
            background-color: #f8f8f8;
            border-left: 4px solid #2d3748;
            border-radius: 4px;
            padding: 16px 20px;
            margin: 24px 0;
        }
        .credentials p {
            margin: 6px 0;
            font-size: 14px;
        }
        .credentials span {
            font-weight: bold;
            color: #2d3748;
        }
        .tip {
            background-color: #fffbeb;
            border: 1px solid #f6e05e;
            border-radius: 4px;
            padding: 14px 18px;
            font-size: 14px;
            color: #744210;
            margin-bottom: 24px;
        }
        .btn-wrapper {
            text-align: center;
            margin: 28px 0;
        }
        .btn {
            display: inline-block;
            background-color: #2d3748;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .footer {
            background-color: #f4f4f4;
            text-align: center;
            padding: 20px 40px;
            font-size: 12px;
            color: #999999;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="container">

        <div class="header">
            <h1>A Warm Welcome to {{ config('app.name') }}!</h1>
        </div>

        <div class="body">
            <p>Hello <strong>{{ $recipientName }}</strong>,</p>

            <p>
                We're delighted to let you know that your account as a
                <strong>{{ $accountType }}</strong> has been set up! You now have full access to
                {{ config('app.name') }} and all the features designed to make your experience
                smooth and easy.
            </p>

            <p>To get started, please use the temporary credentials below to log in:</p>

            <div class="credentials">
                <p><span>Login Email:</span> {{ $email }}</p>
                <p><span>Temporary Password:</span> {{ $tempPassword }}</p>
            </div>

            <div class="tip">
                <strong>Quick Tip:</strong> For your security, please
                <strong>change this temporary password</strong> immediately after your first
                successful login.
            </div>

            <p>Ready to jump in? Click the button below to head straight to the login page:</p>

            <div class="btn-wrapper">
                <a href="{{ $loginUrl }}" class="btn">Start Using {{ config('app.name') }}</a>
            </div>

            <p>
                We're truly excited to have you join our community! If anything comes up,
                please don't hesitate to reach out to the administrator.
            </p>

            <p>
                Thanks and welcome aboard,<br>
                <strong>The Team at {{ config('app.name') }}</strong>
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>

    </div>
</div>
</body>
</html>
