<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; background:#0f172a; color:#e2e8f0; padding:24px;">
    <div style="max-width:560px; margin:0 auto; background:#1e293b; border-radius:12px; padding:24px;">
        <h2 style="color:#ffffff; margin-top:0;">New advertiser inquiry</h2>

        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <tr>
                <td style="padding:6px 0; color:#94a3b8;">Name</td>
                <td style="padding:6px 0; color:#f1f5f9;">{{ $lead->name }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#94a3b8;">Email</td>
                <td style="padding:6px 0; color:#f1f5f9;">{{ $lead->email }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#94a3b8;">Company</td>
                <td style="padding:6px 0; color:#f1f5f9;">{{ $lead->company ?: '—' }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#94a3b8;">Budget range</td>
                <td style="padding:6px 0; color:#f1f5f9;">{{ $lead->budget_range ?: '—' }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#94a3b8; vertical-align:top;">Message</td>
                <td style="padding:6px 0; color:#f1f5f9; white-space:pre-line;">{{ $lead->message }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#94a3b8;">IP</td>
                <td style="padding:6px 0; color:#f1f5f9;">{{ $lead->ip ?: '—' }}</td>
            </tr>
        </table>

        <p style="margin-top:24px;">
            <a href="{{ url('/admin/advertiser-leads') }}" style="color:#60a5fa;">View this lead in the admin panel →</a>
        </p>
    </div>
</body>
</html>
