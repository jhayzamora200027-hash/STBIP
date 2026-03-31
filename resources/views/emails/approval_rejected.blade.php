<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Registration Rejected</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;color:#333;">
    @php
        $appUrl = config('app.url') ?: (request()->getSchemeAndHttpHost() ?? '/');
        $link = $appUrl === '/' ? url('/main') : rtrim($appUrl, '/') . '/';
    @endphp
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:20px;text-align:left;">
                <h2 style="margin:0 0 8px 0;">Registration Update</h2>
                <p style="margin:0 0 16px 0;color:#666;">Hello {{ $user->name }},</p>
                <p style="font-size:15px;line-height:1.5;margin:0 0 12px 0;">We regret to inform you that your registration has been rejected by the administrator.</p>
                @if(!empty($reason))
                <p style="font-size:14px;color:#333;margin:0 0 12px 0;"><strong>Reason:</strong> {{ $reason }}</p>
                @endif
                <p style="font-size:14px;color:#666;margin:18px 0 0 0;">If you believe this is an error or would like more information, please contact <strong>{{ $adminEmail ?? 'support' }}</strong>.</p>
                <p style="font-size:14px;color:#666;margin:6px 0 0 0;">Regards,
                <p style="font-size:14px;color:#666;margin:18px 0 0 0;">Link: {{ $link }}</p>
                @include('emails.partials.footer')
            </td>
        </tr>
    </table>
</body>
</html>
