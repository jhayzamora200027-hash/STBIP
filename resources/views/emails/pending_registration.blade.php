<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pending Registrations</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:20px;text-align:left;">
                <img src="{{ asset('images/dattachments/social technology bureau innovating solution logo.png') }}" alt="STB Portal" style="width:64px;height:64px;border-radius:8px;display:block;margin-bottom:12px;">
                <h2 style="margin:0 0 8px 0;">STB Portal</h2>
                <p style="margin:0 0 16px 0;color:#666;">Automated notification from STB Inventory Portal</p>
                <hr style="border:none;border-top:1px solid #eee;margin:12px 0;">
                <p style="font-size:15px;line-height:1.5;margin:0 0 12px 0;">Greetings!</p>
                <p style="font-size:15px;line-height:1.5;margin:0 0 12px 0;">This is to inform you that there are <strong>{{ $pendingCount }}</strong> account(s) currently pending approval. Please review the pending registrations at your earliest convenience.</p>
                <p style="font-size:14px;color:#666;margin:18px 0 0 0;">Regards,<br>STB Inventory Portal</p>

                @include('emails.partials.footer')
            </td>
        </tr>
    </table>
</body>
</html>
