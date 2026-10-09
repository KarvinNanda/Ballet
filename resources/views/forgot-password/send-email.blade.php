<!DOCTYPE html>
<html lang="id">
<body style="margin:0;padding:24px;background:#F8FAFC;font-family:Arial,Helvetica,sans-serif;color:#0F172A;">
    <table role="presentation" width="100%" style="max-width:480px;margin:0 auto;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:12px;">
        <tr><td style="padding:28px;">
            <p style="font-size:20px;font-weight:bold;margin:0 0 16px;">En Pointe</p>
            @if ($welcome ?? false)
                <p style="margin:0 0 16px;">Akun kamu sudah dibuat. Buat password kamu lewat tombol di bawah.</p>
            @else
                <p style="margin:0 0 16px;">Kami menerima permintaan untuk membuat password baru untuk akun kamu.</p>
            @endif
            <p style="margin:0 0 24px;">
                <a href="{{ $url }}" style="display:inline-block;background:#BE185D;color:#FFFFFF;text-decoration:none;font-weight:bold;padding:12px 20px;border-radius:8px;">Buat password baru</a>
            </p>
            <p style="margin:0 0 8px;color:#64748B;font-size:13px;">Link berlaku 30 menit dan hanya bisa dipakai sekali.</p>
            @if ($welcome ?? false)
                <p style="margin:0;color:#64748B;font-size:13px;">Kalau link sudah tidak berlaku, buka halaman login lalu pilih "Lupa password" dengan email ini.</p>
            @else
                <p style="margin:0;color:#64748B;font-size:13px;">Kalau kamu tidak meminta ini, abaikan email ini.</p>
            @endif
        </td></tr>
    </table>
</body>
</html>
