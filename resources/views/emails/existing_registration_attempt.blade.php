<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Registration Attempt Notice</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:20px;text-align:left;">
                <h2 style="margin:0 0 8px 0;">STB Inventory Portal</h2>
                <p style="margin:0 0 16px 0;color:#666;">Automated security notification from STB Inventory Portal</p>
                <hr style="border:none;border-top:1px solid #eee;margin:12px 0;">
                <p style="font-size:15px;line-height:1.5;margin:0 0 12px 0;">Hello,</p>
                <p style="font-size:15px;line-height:1.5;margin:0 0 12px 0;">A registration attempt was made using this email address on the STB Inventory Portal.</p>
                <p style="font-size:15px;line-height:1.5;margin:0 0 12px 0;">If this was you, no further action is needed you no longer needed to register again as </p>
                <p style="font-size:15px;line-height:1.5;margin:0 0 12px 0;">your account is already active. You can simply log in to the portal using your existing credentials.</p>
                <p style="font-size:15px;line-height:1.5;margin:0 0 12px 0;"> If you did not initiate this attempt, please contact your administrator immediately and consider changing your account password to secure your access. For assistance, contact your administrator</p>
                @php
                    $appUrl = config('app.url') ?: (request()->getSchemeAndHttpHost() ?? '/');
                    $link = $appUrl === '/' ? url('/main') : rtrim($appUrl, '/') . '/';
                @endphp
                <p style="font-size:14px;color:#666;margin:18px 0 0 0;">Portal: {{ $link }}</p>

                @include('emails.partials.footer')
            </td>
        </tr>
    </table>
</body>
</html>
