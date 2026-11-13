@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('topbar_eyebrow', 'Ruang administrator')
@section('topbar_title', 'Rekapitulasi hasil asesmen')

@section('content')
    <section class="admin-report-dashboard" aria-labelledby="dashboard-heading">
        <div class="dashboard-report-header">
            <div>
                <p class="eyebrow">Ringkasan asesmen</p>
                <h1 id="dashboard-heading">Rekapitulasi Data Peserta PELITA</h1>
                <p class="muted">Periode data: <strong>{{ $periodLabel }}</strong></p>
            </div>
            <div class="dashboard-report-actions">
                <a class="button button-secondary" href="{{ route('admin.dashboard.export') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3"/></svg>
                    Unduh rekap PDF
                </a>
                <a class="button" href="{{ route('admin.participants.index') }}">Data peserta <span aria-hidden="true">→</span></a>
            </div>
        </div>

        <div class="report-stat-grid">
            <article class="report-stat-card">
                <span class="report-stat-icon report-stat-icon-purple" aria-hidden="true">♙</span>
                <span class="report-stat-label">Total peserta</span>
                <strong>{{ number_format($totalParticipants) }}</strong>
                <small>Peserta dengan asesmen selesai</small>
            </article>
            <article class="report-stat-card">
                <span class="report-stat-icon report-stat-icon-blue" aria-hidden="true">✓</span>
                <span class="report-stat-label">Asesmen selesai</span>
                <strong>{{ number_format($totalAttempts) }}</strong>
                <small>Hasil yang sudah dinilai</small>
            </article>
            <article class="report-stat-card">
                <span class="report-stat-icon report-stat-icon-gold" aria-hidden="true">◈</span>
                <span class="report-stat-label">Rata-rata skor</span>
                <strong>{{ $averageScore ?? '—' }}<small>{{ $averageScore !== null ? ' / 100' : '' }}</small></strong>
                <small>Dari seluruh asesmen selesai</small>
            </article>
            <article class="report-stat-card">
                <span class="report-stat-icon report-stat-icon-pink" aria-hidden="true">★</span>
                <span class="report-stat-label">Level terbanyak</span>
                <strong class="report-stat-level">{{ $dominantLevel ? $dominantLevel['name'] : '—' }}</strong>
                <small>{{ $dominantLevel ? number_format($dominantLevel['percentage'], 0).'% dari hasil selesai' : 'Belum ada hasil asesmen' }}</small>
            </article>
        </div>

        <div class="report-analysis-grid">
            <section class="report-panel" aria-labelledby="maturity-heading">
                <div class="report-panel-heading">
                    <div>
                        <p class="eyebrow">Klasifikasi peserta</p>
                        <h2 id="maturity-heading">Distribusi Tingkat Kematangan</h2>
                    </div>
                </div>
                @if ($totalAttempts > 0)
                    <div class="report-distribution-content">
                        <div class="report-donut" style="background: {{ $distributionGradient }}" role="img" aria-label="Distribusi tingkat kematangan untuk {{ number_format($totalAttempts) }} asesmen selesai">
                            <span><strong>{{ number_format($totalAttempts) }}</strong><small>asesmen</small></span>
                        </div>
                        <div class="report-legend">
                            @foreach ($levels as $level)
                                @php($share = $totalAttempts > 0 ? $level['count'] / $totalAttempts * 100 : 0)
                                <div class="report-legend-item">
                                    <span class="report-legend-dot" style="background: {{ $level['color'] }}"></span>
                                    <span>{{ $level['name'] }}</span>
                                    <strong>{{ number_format($level['count']) }}</strong>
                                    <small>{{ number_format($share, 0) }}%</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="empty-state">Distribusi akan muncul setelah hasil asesmen tersedia.</div>
                @endif
            </section>

            <section class="report-panel" aria-labelledby="dimension-average-heading">
                <div class="report-panel-heading">
                    <div>
                        <p class="eyebrow">Kinerja dimensi</p>
                        <h2 id="dimension-average-heading">Rata-rata Skor per Dimensi</h2>
                    </div>
                </div>
                @forelse ($dimensionAverages as $dimension)
                    @php($score = min(100, max(0, (float) $dimension->average_score)))
                    <div class="report-dimension-row">
                        <div><span>{{ $dimension->dimension_name_snapshot }}</span><strong>{{ number_format((float) $dimension->average_score, 2) }}</strong></div>
                        <div class="report-dimension-track" role="img" aria-label="{{ $dimension->dimension_name_snapshot }}: {{ number_format((float) $dimension->average_score, 2) }} dari 100">
                            <span style="width: {{ $score }}%"></span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Rata-rata dimensi akan muncul setelah asesmen selesai.</div>
                @endforelse
                <p class="report-chart-caption">Skor setiap dimensi berada pada rentang 0–100.</p>
            </section>
        </div>

        <section class="report-panel report-latest" aria-labelledby="latest-heading">
            <div class="report-panel-heading">
                <div>
                    <p class="eyebrow">Data terbaru</p>
                    <h2 id="latest-heading">Hasil Asesmen Terbaru</h2>
                </div>
                <a class="report-link" href="{{ route('admin.participants.index') }}">Lihat semua peserta <span aria-hidden="true">→</span></a>
            </div>
            @if ($latestAttempts->isEmpty())
                <div class="empty-state">Hasil peserta yang selesai akan tampil di sini.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Peserta</th><th>Email</th><th>Tanggal selesai</th><th>Skor akhir</th><th>Tingkat kematangan</th><th><span class="sr-only">Detail</span></th></tr>
                        </thead>
                        <tbody>
                            @foreach ($latestAttempts as $attempt)
                                <tr>
                                    <td><strong>{{ $attempt->participant->name }}</strong></td>
                                    <td>{{ $attempt->participant->email }}</td>
                                    <td>{{ $attempt->completed_at?->format('d M Y, H:i') }}</td>
                                    <td><strong>{{ number_format((float) $attempt->total_score, 2) }}</strong></td>
                                    <td><span class="table-level">{{ $attempt->maturity_level }}</span></td>
                                    <td><a class="report-link" href="{{ route('admin.participants.show', $attempt) }}">Detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <p class="report-data-note">Rekapitulasi ini mencakup asesmen berstatus selesai yang telah memiliki skor akhir.</p>
        </section>
    </section>
@endsection
