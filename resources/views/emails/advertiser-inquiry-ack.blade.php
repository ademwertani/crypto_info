<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; background:#0f172a; color:#e2e8f0; padding:24px;">
    <div style="max-width:560px; margin:0 auto; background:#1e293b; border-radius:12px; padding:24px;">
        <h2 style="color:#ffffff; margin-top:0;">Thanks for reaching out, {{ $lead->name }}!</h2>

        <p style="font-size:14px; line-height:1.6; color:#cbd5e1;">
            We've received your advertising inquiry and someone from the CryptoInfo team will get back to you shortly.
        </p>

        <p style="font-size:13px; line-height:1.6; color:#94a3b8; border-top:1px solid #334155; padding-top:16px; margin-top:16px;">
            <strong style="color:#cbd5e1;">Your message:</strong><br>
            <span style="white-space:pre-line;">{{ $lead->message }}</span>
        </p>

        <p style="font-size:12px; color:#64748b; margin-top:24px;">
            This is an automated acknowledgement — no need to reply to this email.
        </p>
    </div>
</body>
</html>
