<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Peserta PELITA</title>
    <style>
        @page { size: a4 landscape; margin: 28px 34px 34px; }
        body { margin: 0; color: #28224b; font-family: DejaVu Sans, sans-serif; font-size: 8px; line-height: 1.45; }
        table { width: 100%; border-collapse: collapse; }
        .top-band { height: 6px; background: #6243c6; }
        .header { margin: 10px 0 15px; }
        .brand { width: 41%; color: #34266d; }
        .brand-mark { color: #6243c6; font-size: 23px; font-weight: bold; vertical-align: middle; }
        .brand-name { margin-left: 5px; font-size: 19px; font-weight: bold; letter-spacing: .5px; }
        .brand-caption { margin: 3px 0 0 28px; color: #77738b; font-size: 7px; }
        .report-heading { border-left: 1px solid #dcd5f1; padding-left: 14px; }
        .report-heading strong { color: #30265e; font-size: 14px; }
        .report-heading span { display: block; margin-top: 3px; color: #77738b; font-size: 8px; }
        .meta { margin-bottom: 10px; color: #77738b; font-size: 7px; }
        .meta strong { color: #4a4563; }
        .filter-label { display: inline-block; margin: 0 4px 3px 0; border: 1px solid #e8e4f2; border-radius: 10px; padding: 3px 7px; color: #5c537e; background: #faf9ff; }
        .table-wrap { border: 1px solid #e4e0ed; border-radius: 7px; }
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data-table thead { display: table-header-group; }
        .data-table th { border-bottom: 1px solid #dcd7eb; padding: 8px 7px; color: #706b86; background: #f7f5fc; font-size: 6px; font-weight: bold; letter-spacing: .04em; text-align: left; text-transform: uppercase; }
        .data-table td { border-bottom: 1px solid #efedf4; padding: 8px 7px; color: #4a4662; font-size: 7px; vertical-align: top; word-wrap: break-word; }
        .data-table tbody tr { page-break-inside: avoid; }
        .data-table tbody tr:last-child td { border-bottom: 0; }
        .data-table .number, .data-table .score { text-align: center; }
        .empty-row { padding: 20px !important; color: #88849a !important; text-align: center; }
        .closing { margin-top: 16px; }
        .closing td { width: 50%; vertical-align: bottom; }
        .note { padding-right: 24px; color: #77738b; font-size: 7px; }
        .note strong { color: #5b5573; }
        .sign { width: 190px; margin-left: auto; color: #625d77; font-size: 7px; text-align: center; }
        .sign-line { height: 30px; border-bottom: 1px solid #c8c2d9; }
        .sign-name { margin-top: 4px; color: #3d365d; font-weight: bold; }
        .footer { margin-top: 10px; border-top: 1px solid #e3deef; padding-top: 6px; color: #9995a9; font-size: 6px; }
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
                <strong>REKAP DATA PESERTA</strong>
                <span>Daftar hasil asesmen yang telah selesai</span>
            </td>
        </tr>
    </table>

    <div class="meta">
        <strong>{{ number_format($attempts->count()) }} hasil</strong>
        · Dicetak {{ $generatedAt->locale('id')->translatedFormat('d F Y, H:i') }}
        @if (filled($filters['search'] ?? null))
            <span class="filter-label">Pencarian: {{ $filters['search'] }}</span>
        @endif
        @if (filled($filters['level'] ?? null))
            <span class="filter-label">Tingkat: {{ $filters['level'] }}</span>
        @endif
        @if (filled($filters['date_from'] ?? null))
            <span class="filter-label">Dari: {{ \Illuminate\Support\Carbon::parse($filters['date_from'])->format('d/m/Y') }}</span>
        @endif
        @if (filled($filters['date_to'] ?? null))
            <span class="filter-label">Sampai: {{ \Illuminate\Support\Carbon::parse($filters['date_to'])->format('d/m/Y') }}</span>
        @endif
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%" class="number">No.</th>
                    <th style="width: 18%">Nama peserta</th>
                    <th style="width: 24%">Email</th>
                    <th style="width: 20%">Asesmen</th>
                    <th style="width: 15%">Tanggal selesai</th>
                    <th style="width: 9%" class="score">Skor akhir</th>
                    <th style="width: 10%">Tingkat kematangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attempts as $attempt)
                    <tr>
                        <td class="number">{{ $loop->iteration }}</td>
                        <td>{{ $attempt->participant->name }}</td>
                        <td>{{ $attempt->participant->email }}</td>
                        <td>{{ $attempt->assessment->title }}</td>
                        <td>{{ $attempt->completed_at?->format('d/m/Y H:i') }}</td>
                        <td class="score">{{ number_format((float) $attempt->total_score, 2) }}</td>
                        <td>{{ $attempt->maturity_level }}</td>
                    </tr>
                @empty
                    <tr><td class="empty-row" colspan="7">Tidak ada hasil yang sesuai dengan filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <table class="closing">
        <tr>
            <td class="note"><strong>Catatan:</strong> Dokumen ini memuat hasil asesmen yang sudah selesai dan sesuai filter pada halaman Data Peserta. Jaga kerahasiaan informasi pribadi yang tercantum di dalam laporan.</td>
            <td>
                <div class="sign">Administrator PELITA<div class="sign-line"></div><div class="sign-name">PELITA</div></div>
            </td>
        </tr>
    </table>
    <div class="footer">PELITA · Pemetaan Literasi dan Kesiapan Teknologi Artificial Intelligence</div>
</body>
</html>
