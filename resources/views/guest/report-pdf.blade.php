<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil Asesmen PELITA</title>
    <style>
        @page { margin: 24px 28px 30px; }
        body { margin: 0; color: #242044; font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.45; }
        table { width: 100%; border-collapse: collapse; }
        .top-band { height: 7px; background: #5836c9; }
        .header { margin: 10px 0 12px; }
        .brand { width: 42%; color: #21164f; }
        .brand-mark { display: inline-block; margin-right: 7px; color: #5836c9; font-size: 25px; font-weight: bold; vertical-align: middle; }
        .brand-name { font-size: 21px; font-weight: bold; letter-spacing: .5px; }
        .brand-caption { margin-left: 35px; color: #77748f; font-size: 7px; }
        .report-heading { border-left: 1px solid #d9d2f3; padding-left: 17px; }
        .report-heading strong { color: #21164f; font-size: 15px; }
        .report-heading span { display: block; margin-top: 3px; color: #77748f; font-size: 9px; }
        .section-label { margin: 16px 0 7px; border-radius: 9px; padding: 7px 10px; color: #fff; background: #5836c9; font-size: 10px; font-weight: bold; }
        .summary-card { border: 1px solid #e5e0f4; border-radius: 10px; padding: 12px; }
        .info-label { width: 34%; color: #77748f; }
        .info-value { color: #242044; font-weight: bold; }
        .score-card { border: 1px solid #e5e0f4; border-radius: 10px; padding: 12px; background: #f2efff; text-align: center; }
        .score-label { color: #514778; font-size: 9px; font-weight: bold; }
        .score-number { margin: 3px 0; color: #21164f; font-size: 29px; font-weight: bold; }
        .score-number small { color: #77748f; font-size: 12px; }
        .level-pill { display: inline-block; border-radius: 20px; padding: 5px 12px; color: #fff; background: #5836c9; font-size: 9px; font-weight: bold; }
        .summary-message { margin-top: 9px; border: 1px solid #e3dcfa; border-radius: 9px; padding: 10px 12px; color: #433775; background: #f5f2ff; font-size: 9px; }
        .summary-message strong { color: #5836c9; }
        .two-column { table-layout: fixed; }
        .two-column > tbody > tr > td { width: 50%; vertical-align: top; }
        .two-column > tbody > tr > td:first-child { padding-right: 5px; }
        .two-column > tbody > tr > td:last-child { padding-left: 5px; }
        .dimension-card { margin-bottom: 7px; border: 1px solid #e6e1f3; border-radius: 8px; padding: 9px; page-break-inside: avoid; }
        .dimension-title { color: #30265e; font-size: 9px; font-weight: bold; }
        .dimension-score { float: right; color: #3a7b5c; font-size: 8px; font-weight: bold; }
        .dimension-score.needs-work { color: #9a5c1f; }
        .bar { height: 6px; margin: 6px 0; border-radius: 4px; background: #ece9f4; }
        .bar-fill { height: 6px; border-radius: 4px; background: #7955dc; }
        .dimension-note { color: #77748f; font-size: 7px; }
        .weight-note { color: #77748f; font-size: 7px; text-align: right; }
        .recommendations { border: 1px solid #e6e1f3; border-radius: 9px; padding: 10px 12px; background: #faf9ff; }
        .recommendation-row td { border-bottom: 1px solid #ece8f5; padding: 7px 3px; vertical-align: top; }
        .recommendation-row:last-child td { border-bottom: 0; }
        .recommendation-number { width: 22px; color: #5836c9; font-size: 11px; font-weight: bold; }
        .recommendation-title { color: #30265e; font-weight: bold; }
        .recommendation-description { margin-top: 2px; color: #77748f; font-size: 8px; }
        .conclusion { border: 1px solid #e5e0f4; border-radius: 9px; padding: 11px 12px; color: #4a4662; background: #fff; }
        .footer { margin-top: 14px; border-top: 1px solid #d9d2f3; padding-top: 8px; color: #77748f; font-size: 7px; }
        .footer-brand { color: #5836c9; font-size: 11px; font-weight: bold; }
        .disclaimer { margin-top: 7px; color: #9995a9; font-size: 7px; }
    </style>
</head>
<body>
    <div class="top-band"></div>
    <table class="header">
        <tr>
            <td class="brand">
                <span class="brand-mark">✦</span><span class="brand-name">PELITA</span>
                <div class="brand-caption">Pemetaan Literasi dan Kesiapan Teknologi AI</div>
            </td>
            <td class="report-heading">
                <strong>LAPORAN HASIL ASESMEN</strong>
                <span>Literasi dan Kesiapan Teknologi AI</span>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td style="width: 57%; padding-right: 6px; vertical-align: top">
                <div class="section-label">Informasi Peserta</div>
                <div class="summary-card">
                    <table>
                        <tr><td class="info-label">Nama Lengkap</td><td class="info-value">{{ $attempt->participant->name }}</td></tr>
                        <tr><td class="info-label">Email</td><td class="info-value">{{ $attempt->participant->email }}</td></tr>
                        <tr><td class="info-label">ID Laporan</td><td class="info-value">{{ $reportId }}</td></tr>
                        <tr><td class="info-label">Tanggal Asesmen</td><td class="info-value">{{ $attempt->completed_at?->format('d F Y') }}</td></tr>
                        <tr><td class="info-label">Asesmen</td><td class="info-value">{{ $attempt->assessment->title }}</td></tr>
                        <tr><td class="info-label">Status</td><td class="info-value">Selesai</td></tr>
                    </table>
                </div>
            </td>
            <td style="width: 43%; padding-left: 6px; vertical-align: bottom">
                <div class="score-card">
                    <div class="score-label">Skor Akhir</div>
                    <div class="score-number">{{ number_format((float) $attempt->total_score, 0) }}<small> / 100</small></div>
                    <div class="level-pill">TINGKAT {{ strtoupper($attempt->maturity_level) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="summary-message"><strong>Ringkasan:</strong> {{ $summary }}</div>

    <table class="two-column" style="margin-top: 7px">
        <tr>
            <td>
                <div class="section-label">Skor per Dimensi</div>
                @foreach ($attempt->dimensionScores as $dimensionScore)
                    @php
                        $dimensionValue = (float) $dimensionScore->score;
                        $needsWork = $dimensionValue < 60;
                        $dimensionNote = $dimensionValue >= 80
                            ? 'Pemahaman dan kesiapan berkembang dengan baik.'
                            : ($needsWork ? 'Menjadi prioritas untuk pengembangan berikutnya.' : 'Sudah cukup baik dan masih dapat ditingkatkan.');
                    @endphp
                    <div class="dimension-card">
                        <span class="dimension-title">{{ $dimensionScore->dimension_name_snapshot }}</span>
                        <span class="dimension-score {{ $needsWork ? 'needs-work' : '' }}">Skor: {{ number_format($dimensionValue, 0) }} | {{ $needsWork ? 'Perlu Peningkatan' : 'Baik' }}</span>
                        <div class="bar"><div class="bar-fill" style="width: {{ min(100, max(0, $dimensionValue)) }}%"></div></div>
                        <table><tr><td class="dimension-note">{{ $dimensionNote }}</td><td class="weight-note">Bobot {{ number_format((float) $dimensionScore->weight_snapshot, 0) }}%</td></tr></table>
                    </div>
                @endforeach
            </td>
            <td>
                <div class="section-label">Analisis Setiap Dimensi</div>
                @foreach ($attempt->dimensionScores as $dimensionScore)
                    @php
                        $dimensionValue = (float) $dimensionScore->score;
                        $needsWork = $dimensionValue < 60;
                    @endphp
                    <div class="dimension-card">
                        <span class="dimension-title">{{ $dimensionScore->dimension_name_snapshot }}</span>
                        <span class="dimension-score {{ $needsWork ? 'needs-work' : '' }}">{{ number_format($dimensionValue, 0) }} / 100</span>
                        <div class="dimension-note">
                            {{ $needsWork
                                ? 'Perlu meningkatkan pemahaman dan penerapan pada aspek ini.'
                                : 'Memiliki bekal yang baik untuk menggunakan teknologi AI secara bijak.' }}
                        </div>
                    </div>
                @endforeach
            </td>
        </tr>
    </table>

    <div class="section-label">Rekomendasi Pengembangan</div>
    <div class="recommendations">
        @forelse ($recommendations as $index => $recommendation)
            <table class="recommendation-row">
                <tr>
                    <td class="recommendation-number">{{ $index + 1 }}.</td>
                    <td>
                        <div class="recommendation-title">{{ $recommendation['title'] ?? 'Rekomendasi pembelajaran' }}</div>
                        <div class="recommendation-description">{{ $recommendation['description'] ?? '' }}</div>
                    </td>
                </tr>
            </table>
        @empty
            <div class="recommendation-description">Pertahankan kebiasaan belajar dan penggunaan AI yang bertanggung jawab. Semua dimensi telah mencapai skor 60 atau lebih.</div>
        @endforelse
    </div>

    <div class="section-label">Kesimpulan</div>
    <div class="conclusion">
        Berdasarkan asesmen PELITA, peserta memperoleh skor akhir
        <strong>{{ number_format((float) $attempt->total_score, 0) }}</strong>
        dengan kategori <strong>{{ $attempt->maturity_level }}</strong>.
        {{ $summary }} Gunakan hasil ini sebagai gambaran awal untuk merencanakan pengembangan diri.
    </div>

    <div class="footer">
        <table>
            <tr>
                <td class="footer-brand">✦ PELITA</td>
                <td>Dicetak pada: {{ now()->format('d F Y') }}</td>
                <td>ID Laporan: {{ $reportId }}</td>
                <td style="text-align: right">Bersama membangun masyarakat melek AI</td>
            </tr>
        </table>
        <div class="disclaimer">Laporan ini merupakan ringkasan hasil asesmen mandiri, bukan sertifikasi atau satu-satunya dasar pengambilan keputusan.</div>
    </div>
</body>
</html>
