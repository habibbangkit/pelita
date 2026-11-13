<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Data Peserta PELITA</title>
    <style>
        @page { size: a4 landscape; margin: 25px 30px 28px; }
        body { margin: 0; color: #28224b; font-family: DejaVu Sans, sans-serif; font-size: 8px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        .top-band { height: 6px; background: #6243c6; }
        .header { margin: 10px 0 14px; }
        .brand { width: 40%; color: #34266d; }
        .brand-mark { color: #6243c6; font-size: 23px; font-weight: bold; vertical-align: middle; }
        .brand-name { margin-left: 5px; font-size: 19px; font-weight: bold; letter-spacing: .5px; }
        .brand-caption { margin: 3px 0 0 28px; color: #77738b; font-size: 7px; }
        .report-heading { border-left: 1px solid #dcd5f1; padding-left: 14px; }
        .report-heading strong { color: #30265e; font-size: 14px; }
        .report-heading span { display: block; margin-top: 3px; color: #77738b; font-size: 8px; }
        .period { margin: 3px 0 10px; color: #77738b; font-size: 8px; }
        .metrics { table-layout: fixed; }
        .metrics td { width: 25%; padding-right: 7px; vertical-align: top; }
        .metrics td:last-child { padding-right: 0; }
        .metric-card { min-height: 57px; border: 1px solid #e8e4f2; border-radius: 8px; padding: 9px 11px; background: #fff; }
        .metric-label { display: block; color: #77738b; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .metric-value { display: block; margin-top: 4px; color: #33276a; font-size: 18px; font-weight: bold; }
        .metric-value small { color: #88849a; font-size: 8px; }
        .metric-note { display: block; margin-top: 2px; color: #88849a; font-size: 6px; }
        .analysis { table-layout: fixed; margin-top: 10px; }
        .analysis > tbody > tr > td { width: 50%; vertical-align: top; }
        .analysis > tbody > tr > td:first-child { padding-right: 5px; }
        .analysis > tbody > tr > td:last-child { padding-left: 5px; }
        .panel { min-height: 115px; border: 1px solid #e8e4f2; border-radius: 8px; padding: 10px; }
        .section-title { margin: 0 0 9px; color: #35296e; font-size: 9px; font-weight: bold; }
        .distribution-table { width: auto; margin: 0 auto; }
        .distribution-table td { vertical-align: middle; }
        .distribution-legend { padding-left: 12px; }
        .legend-row td { padding: 3px 4px; color: #5f5a75; font-size: 7px; }
        .legend-row strong { color: #3b345c; }
        .legend-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; }
        .dimension-row td { padding: 5px 2px; vertical-align: middle; }
        .dimension-name { width: 38%; color: #5b5671; font-size: 7px; }
        .dimension-score { width: 11%; color: #3b345c; font-size: 7px; font-weight: bold; text-align: right; }
        .dimension-bar-cell { padding-left: 8px !important; }
        .bar { height: 7px; border-radius: 5px; background: #eeebf5; }
        .bar-fill { height: 7px; border-radius: 5px; background: #7658d2; }
        .caption { margin-top: 5px; color: #88849a; font-size: 6px; }
        .latest { margin-top: 10px; }
        .latest-title { margin-bottom: 6px; color: #35296e; font-size: 9px; font-weight: bold; }
        .data-table th { border-bottom: 1px solid #ded9ee; padding: 6px 7px; color: #706b86; background: #f7f5fc; font-size: 6px; font-weight: bold; letter-spacing: .04em; text-align: left; text-transform: uppercase; }
        .data-table td { border-bottom: 1px solid #efedf4; padding: 6px 7px; color: #4a4662; font-size: 7px; }
        .data-table tbody tr:last-child td { border-bottom: 0; }
        .empty-row { padding: 12px !important; color: #88849a !important; text-align: center; }
        .closing { margin-top: 10px; }
        .closing td { width: 50%; vertical-align: bottom; }
        .note { padding-right: 22px; color: #77738b; font-size: 6px; }
        .sign { width: 180px; margin-left: auto; color: #625d77; font-size: 7px; text-align: center; }
        .sign-line { height: 25px; border-bottom: 1px solid #c8c2d9; }
        .sign-name { margin-top: 4px; color: #3d365d; font-weight: bold; }
        .footer { margin-top: 8px; border-top: 1px solid #e3deef; padding-top: 5px; color: #9995a9; font-size: 6px; }
    </style>
</head>
<body>
    <div class="top-band"></div>
    <table class="header">
        <tr>
            <td class="brand">
                <span class="brand-mark">✦</span><span class="brand-name">PELITA</span>
                <div class="brand-caption">Pemetaan Literasi dan Kesiapan Teknologi Artificial Intelligence</div>
            </td>
            <td class="report-heading">
                <strong>REKAPITULASI DATA PESERTA</strong>
                <span>Ringkasan hasil asesmen literasi dan kesiapan AI</span>
            </td>
        </tr>
    </table>
    <div class="period">Periode asesmen: <strong>{{ $periodLabel }}</strong> · Dicetak {{ $generatedAt->locale('id')->translatedFormat('d F Y, H:i') }}</div>

    <table class="metrics">
        <tr>
            <td><div class="metric-card"><span class="metric-label">Total peserta</span><span class="metric-value">{{ number_format($totalParticipants) }}</span><span class="metric-note">Peserta dengan asesmen selesai</span></div></td>
            <td><div class="metric-card"><span class="metric-label">Asesmen selesai</span><span class="metric-value">{{ number_format($totalAttempts) }}</span><span class="metric-note">Hasil yang sudah dinilai</span></div></td>
            <td><div class="metric-card"><span class="metric-label">Rata-rata skor</span><span class="metric-value">{{ $averageScore ?? '—' }}<small>{{ $averageScore !== null ? ' / 100' : '' }}</small></span><span class="metric-note">Dari seluruh asesmen selesai</span></div></td>
            <td><div class="metric-card"><span class="metric-label">Level terbanyak</span><span class="metric-value" style="font-size: 14px">{{ $dominantLevel ? $dominantLevel['name'] : '—' }}</span><span class="metric-note">{{ $dominantLevel ? number_format($dominantLevel['percentage'], 0).'% dari hasil selesai' : 'Belum ada hasil asesmen' }}</span></div></td>
        </tr>
    </table>

    <table class="analysis">
        <tr>
            <td>
                <div class="panel">
                    <div class="section-title">Distribusi Tingkat Kematangan</div>
                    <table class="distribution-table">
                        <tr>
                            <td>
                                <svg width="106" height="106" viewBox="0 0 120 120" role="img" aria-label="Diagram distribusi tingkat kematangan">
                                    <circle cx="60" cy="60" r="44" fill="none" stroke="#eeeaf5" stroke-width="20"/>
                                    @foreach ($levels as $level)
                                        @if ($level['percentage'] > 0)
                                            <circle cx="60" cy="60" r="44" fill="none" stroke="{{ $level['color'] }}" stroke-width="20" stroke-dasharray="{{ number_format($level['dashLength'], 2, '.', '') }} {{ number_format($circumference - $level['dashLength'], 2, '.', '') }}" stroke-dashoffset="-{{ number_format($level['dashOffset'], 2, '.', '') }}" transform="rotate(-90 60 60)"/>
                                        @endif
                                    @endforeach
                                    <text x="60" y="58" fill="#33276a" font-family="DejaVu Sans, sans-serif" font-size="17" font-weight="bold" text-anchor="middle">{{ $totalAttempts }}</text>
                                    <text x="60" y="72" fill="#77738b" font-family="DejaVu Sans, sans-serif" font-size="7" text-anchor="middle">asesmen</text>
                                </svg>
                            </td>
                            <td class="distribution-legend">
                                <table>
                                    @foreach ($levels as $level)
                                        <tr class="legend-row">
                                            <td><span class="legend-dot" style="background: {{ $level['color'] }}"></span></td>
                                            <td>{{ $level['name'] }}</td>
                                            <td><strong>{{ number_format($level['count']) }}</strong></td>
                                            <td>{{ number_format($level['percentage'], 0) }}%</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="panel">
                    <div class="section-title">Rata-rata Skor per Dimensi</div>
                    @forelse ($dimensionAverages as $dimension)
                        @php($score = min(100, max(0, (float) $dimension->average_score)))
                        <table>
                            <tr class="dimension-row">
                                <td class="dimension-name">{{ $dimension->dimension_name_snapshot }}</td>
                                <td class="dimension-score">{{ number_format((float) $dimension->average_score, 1) }}</td>
                                <td class="dimension-bar-cell"><div class="bar"><div class="bar-fill" style="width: {{ $score }}%"></div></div></td>
                            </tr>
                        </table>
                    @empty
                        <div class="caption">Belum ada data skor dimensi.</div>
                    @endforelse
                    <div class="caption">Rentang skor setiap dimensi adalah 0–100.</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="latest">
        <div class="latest-title">Hasil Asesmen Terbaru</div>
        <table class="data-table">
            <thead>
                <tr><th style="width: 23%">Nama peserta</th><th style="width: 27%">Email</th><th style="width: 20%">Tanggal selesai</th><th style="width: 14%">Skor akhir</th><th style="width: 16%">Tingkat kematangan</th></tr>
            </thead>
            <tbody>
                @forelse ($latestAttempts as $attempt)
                    <tr>
                        <td>{{ $attempt->participant->name }}</td>
                        <td>{{ $attempt->participant->email }}</td>
                        <td>{{ $attempt->completed_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ number_format((float) $attempt->total_score, 2) }}</td>
                        <td>{{ $attempt->maturity_level }}</td>
                    </tr>
                @empty
                    <tr><td class="empty-row" colspan="5">Belum ada hasil asesmen yang selesai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <table class="closing">
        <tr>
            <td class="note">Catatan: Rekap ini mencakup asesmen berstatus selesai yang telah memiliki skor akhir. Dokumen ini dihasilkan oleh sistem PELITA dan tidak menggantikan dokumen resmi yang memerlukan pengesahan.</td>
            <td>
                <div class="sign">Administrator PELITA<div class="sign-line"></div><div class="sign-name">PELITA</div></div>
            </td>
        </tr>
    </table>
    <div class="footer">PELITA · Pemetaan Literasi dan Kesiapan Teknologi Artificial Intelligence</div>
</body>
</html>
