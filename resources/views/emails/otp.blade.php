<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif; color:#222; background:#f7f7f7; padding:30px 0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 6px 18px rgba(0,0,0,0.06);">
        <tr>
            <td style="padding:34px 40px;text-align:center">
                <img src="{{ isset($message) ? $message->embed(public_path('images/dattachments/Asset 7@1080x.png')) : asset('images/dattachments/' . rawurlencode('Asset 11@1080x.png')) }}" alt="Logo" style="height:56px;object-fit:contain;margin-bottom:18px;display:block;margin-left:auto;margin-right:auto" />
                <h2 style="font-size:20px;margin:0 0 10px;color:#222;letter-spacing:0.2px">Verify your sign-in</h2>
                <p style="margin:0 0 20px;color:#6b6b6b;font-size:14px;line-height:1.5">We received a sign-in attempt for your account. Please enter the following code in the browser window where you started signing in.</p>

                <div style="margin:22px auto;max-width:520px;background:#f1f5f4;border-radius:10px;padding:22px 16px;text-align:center;">
                    <div style="display:inline-block;padding:18px 36px;background:#ffffff;border-radius:8px;box-shadow:inset 0 0 0 1px rgba(0,0,0,0.03);">
                        <div style="font-family:monospace, 'Courier New', monospace;font-size:34px;letter-spacing:4px;color:#222;font-weight:700">{{ $otp }}</div>
                    </div>
                </div>

                <p style="color:#9aa0a6;font-size:13px;margin-top:6px;">If you did not attempt to sign in but received this email, please disregard it. The code will remain active for 5 minutes.</p>
                <hr style="border:none;border-top:1px solid #eef2f3;margin:26px 0" />
                <p style="color:#9aa0a6;font-size:12px;margin:0">STB Inventory Portal — an effortless inventory solution with the features you need.</p>
                <div style="margin-top:12px;color:#b0b7bb;font-size:12px">© {{ date('Y') }} STBIP. All rights reserved.</div>
            </td>
        </tr>
    </table>
</div>
