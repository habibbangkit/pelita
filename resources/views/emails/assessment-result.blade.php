<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hasil Asesmen PELITA</title>
</head>
<body style="margin:0;padding:24px;background:#f7f6fc;color:#242044;font-family:Arial,sans-serif">
    <table role="presentation" style="width:100%;max-width:620px;margin:0 auto;border-collapse:collapse;background:#fff;border:1px solid #e9e6f4;border-radius:16px">
        <tr>
            <td style="padding:24px 28px;background:#21164f;color:#fff;border-radius:16px 16px 0 0">
                <div style="font-size:22px;font-weight:bold;letter-spacing:1px">PELITA</div>
                <div style="margin-top:5px;color:#ded8f5;font-size:13px">Laporan hasil asesmen literasi dan kesiapan AI</div>
            </td>
        </tr>
        <tr>
            <td style="padding:28px">
                <p style="margin:0 0 14px;font-size:16px">Halo {{ $attempt->participant->name }},</p>
                <p style="margin:0 0 18px;color:#625e78;line-height:1.6">
                    Terima kasih telah menyelesaikan asesmen PELITA. Ringkasan hasil Anda:
                </p>
                <table role="presentation" style="width:100%;margin-bottom:20px;border-collapse:collapse;background:#f5f2ff;border-radius:12px">
                    <tr>
                        <td style="width:50%;padding:16px">
                            <div style="color:#77748f;font-size:12px">Skor akhir</div>
                            <strong style="display:block;margin-top:4px;color:#21164f;font-size:25px">{{ number_format((float) $attempt->total_score, 2) }} / 100</strong>
                        </td>
                        <td style="padding:16px">
                            <div style="color:#77748f;font-size:12px">Tingkat kematangan</div>
                            <strong style="display:block;margin-top:8px;color:#5836c9;font-size:16px">{{ $attempt->maturity_level }}</strong>
                        </td>
                    </tr>
                </table>
                <p style="margin:0 0 18px;color:#625e78;line-height:1.6">
                    Laporan lengkap dalam format PDF terlampir pada email ini.
                </p>
                <p style="margin:0;color:#77748f;font-size:13px;line-height:1.6">
                    Email ini dikirim ke alamat yang Anda berikan saat mendaftar asesmen. Jika Anda tidak merasa mengikuti asesmen ini, silakan hubungi pengelola melalui informasi kontak pada laporan.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 28px;border-top:1px solid #e9e6f4;color:#9995a9;font-size:12px">
                PELITA · Pemetaan Literasi dan Kesiapan Teknologi AI
            </td>
        </tr>
    </table>
</body>
</html>
