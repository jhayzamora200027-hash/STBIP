<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Registration Approved</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;color:#333;">
    
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:20px;text-align:left;">
                <h2 style="margin:0 0 8px 0;">Registration Approved</h2>
                <p style="margin:0 0 16px 0;color:#666;">Hello {{ $user->name }},</p>
                <p style="font-size:15px;line-height:1.5;margin:0 0 12px 0;">Good news! Your registration has been approved by the administrator.</p>
                <p style="font-size:15px;line-height:1.5;margin:0 0 12px 0;">You can now log in using your email: <strong>{{ $user->email }}</strong></p>
                <p style="font-size:14px;color:#666;margin:18px 0 0 0;">Regards,<br>STB Inventory Portal</p>
                @php
                        $appUrl = config('app.url') ?: (request()->getSchemeAndHttpHost() ?? '/');
                        $link = $appUrl === '/' ? url('/main') : rtrim($appUrl, '/') . '/';
                @endphp
                <p style="font-size:14px;color:#666;margin:18px 0 0 0;">Link: {{ $link }}</p>
                @include('emails.partials.footer')
            </td>
        </tr>
    </table>
</body>
</html>
