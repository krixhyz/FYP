<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background-color: #f3f3f3; color: #1a1c1c; line-height: 1.6; }
        .wrapper { max-width: 600px; margin: 32px auto; padding: 0 16px 32px; }
        .header { background-color: #006a38; padding: 24px 32px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 22px; letter-spacing: 2px; text-transform: uppercase; }
        .card { background-color: #ffffff; padding: 40px 40px 32px; }
        .greeting { font-size: 18px; font-weight: bold; margin-bottom: 16px; color: #1a1c1c; }
        .body-text { font-size: 15px; color: #444746; margin-bottom: 20px; line-height: 1.7; }
        .btn-wrap { text-align: center; margin: 32px 0; }
        .btn {
            display: inline-block;
            background-color: #006a38;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 14px 36px;
        }
        .notice { background-color: #f9f9f9; border-left: 4px solid #006a38; padding: 14px 18px; margin-top: 28px; }
        .notice p { font-size: 13px; color: #666; margin: 0; }
        .divider { border: none; border-top: 1px solid #e5e5e5; margin: 32px 0 20px; }
        .footer { text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        <div class="card">
            <p class="greeting">Hi {{ $userName }},</p>

            <p class="body-text">
                Welcome to {{ config('app.name') }}! Please verify your email address to activate your account
                and start buying, renting, and swapping on the marketplace.
            </p>

            <div class="btn-wrap">
                <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
            </div>

            <p class="body-text" style="font-size:13px; color:#888; text-align:center;">
                This link expires in <strong>15 minutes</strong>. If it expires, you can request a new one from the app.
            </p>

            <div class="notice">
                <p>If you did not create an account on {{ config('app.name') }}, no action is required — you can safely ignore this email.</p>
            </div>

            <hr class="divider">

            <div class="footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
