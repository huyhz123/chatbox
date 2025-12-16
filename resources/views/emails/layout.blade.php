<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Email from ' . config('app.name'))</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #51545e;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f4f4f7;
            padding: 40px 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body h2 {
            color: #333333;
            font-size: 22px;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .email-body p {
            margin: 15px 0;
            font-size: 16px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            opacity: 0.9;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333333;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #667eea;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0 !important;
            }
            .email-body {
                padding: 20px !important;
            }
            .button {
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <h1>{{ config('app.name') }}</h1>
            </div>

            <!-- Body -->
            <div class="email-body">
                @yield('content')
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p>{{ config('app.url') }}</p>

                <div class="social-links">
                    @yield('social_links')
                </div>

                <p style="margin-top: 20px; font-size: 12px; color: #999;">
                    If you have any questions, contact us at <a href="mailto:support@example.com">support@example.com</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
