<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('subject')</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #F8F3EC;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #3A2E22;
        }
        h1, h2, h3 {
            font-family: Georgia, 'Times New Roman', serif;
            color: #5C3A21;
            margin: 0 0 12px;
        }
        .wrapper {
            width: 100%;
            background-color: #F8F3EC;
            padding: 32px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #E7DCCB;
        }
        .header {
            background-color: #8B5E3C;
            padding: 24px 32px;
            text-align: center;
        }
        .header h1 {
            color: #FFFFFF;
            font-size: 22px;
            letter-spacing: 0.5px;
        }
        .body {
            padding: 32px;
        }
        .card {
            background-color: #F8F3EC;
            border: 1px solid #E7DCCB;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #8B5E3C;
            color: #FFFFFF !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: bold;
            margin: 16px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
        }
        table.data-table th, table.data-table td {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid #E7DCCB;
            font-size: 14px;
        }
        table.data-table th {
            color: #8B5E3C;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-success { background-color: #E4EFE3; color: #2F6B33; }
        .badge-warning { background-color: #FBEFDA; color: #9A6A18; }
        .badge-info { background-color: #E5EEF5; color: #2A5A82; }
        .footer {
            padding: 24px 32px;
            text-align: center;
            font-size: 12px;
            color: #8A7A66;
            border-top: 1px solid #E7DCCB;
        }
        .footer a {
            color: #8B5E3C;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>{{ settings('site_name', 'Himashva') }}</h1>
            </div>
            <div class="body">
                @yield('content')
            </div>
            <div class="footer">
                <p>
                    Need help? Contact us
                    @if (settings('contact_email'))
                        at <a href="mailto:{{ settings('contact_email') }}">{{ settings('contact_email') }}</a>
                    @endif
                    @if (settings('contact_phone'))
                        or call {{ settings('contact_phone') }}
                    @endif
                    @if (settings('whatsapp_number'))
                        or WhatsApp us at {{ settings('whatsapp_number') }}
                    @endif
                </p>
                <p>&copy; {{ now()->year }} {{ settings('site_name', 'Himashva') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
