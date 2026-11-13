@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('topbar_eyebrow', 'Ruang administrator')
@section('topbar_title', 'Dashboard pemetaan literasi AI')

@section('content')
    <section class="dashboard-page" aria-labelledby="dashboard-heading">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Ringkasan asesmen</p>
                <h1 id="dashboard-heading">Dashboard PELITA</h1>
                <p class="muted">Pantau hasil asesmen dan tingkat kesiapan peserta.</p>
            </div>
            <a class="button" href="{{ route('admin.participants.index') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"/><path d="M2 19h21"/></svg>
                Lihat data peserta
            </a>
        </div>

        <div class="stat-grid">
            <article class="stat-card stat-purple">
                <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5.5a3 3 0 0 1 0 5.8M18 14a4 4 0 0 1 3 4v2"/></svg></span>
                <span class="stat-label">Total peserta</span>
                <strong class="stat-value">{{ number_format($totalParticipants) }}</strong>
                <span class="stat-caption">Email unik terdaftar</span>
            </article>
            <article class="stat-card stat-blue">
                <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"/><path d="M2 19h21"/></svg></span>
                <span class="stat-label">Asesmen selesai</span>
                <strong class="stat-value">{{ number_format($totalAttempts) }}</strong>
                <span class="stat-caption">Hasil yang sudah dinilai</span>
            </article>
            <article class="stat-card stat-gold">
                <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4ZM7 6H4v2a4 4 0 0 0 4 4m9-6h3v2a4 4 0 0 1-4 4"/></svg></span>
                <span class="stat-label">Rata-rata skor</span>
                <strong class="stat-value">{{ $averageScore ?? '—' }}<small>{{ $averageScore !== null ? ' / 100' : '' }}</small></strong>
                <span class="stat-caption">Dari seluruh asesmen selesai</span>
            </article>
        </div>

        <div class="dashboard-grid">
            <section class="card distribution-card" aria-labelledby="distribution-heading">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Klasifikasi peserta</p>
                        <h2 id="distribution-heading">Distribusi tingkat kematangan</h2>
                    </div>
                    <span class="section-icon" aria-hidden="true">◉</span>
                </div>
                @forelse ($levels as $level)
                    @php($share = $totalAttempts > 0 ? round(($level['count'] / $totalAttempts) * 100) : 0)
                    <div class="level-row">
                        <div class="level-row-heading">
                            <span class="level-dot level-dot-{{ \Illuminate\Support\Str::slug($level['name']) }}"></span>
                            <span>{{ $level['name'] }}</span>
                            <strong>{{ number_format($level['count']) }}</strong>
                            <small>{{ $share }}%</small>
                        </div>
                        <div class="progress" role="img" aria-label="{{ $level['name'] }}: {{ $share }} persen">
                            <span style="width: {{ $share }}%"></span>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">Belum ada level kematangan aktif.</div>
                @endforelse
                @if ($totalAttempts === 0)
                    <p class="result-caption">Distribusi akan muncul setelah hasil asesmen tersedia.</p>
                @endif
            </section>

            <section class="card latest-card" aria-labelledby="latest-heading">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Aktivitas terbaru</p>
                        <h2 id="latest-heading">Hasil asesmen terbaru</h2>
                    </div>
                    <a class="text-link" href="{{ route('admin.participants.index') }}">Semua hasil <span aria-hidden="true">→</span></a>
                </div>
                @if ($latestAttempts->isEmpty())
                    <div class="empty-state">
                        <span class="empty-state-icon" aria-hidden="true">◌</span>
                        <strong>Belum ada hasil</strong>
                        <span>Hasil peserta yang selesai akan tampil di sini.</span>
                    </div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Peserta</th>
                                    <th>Skor</th>
                                    <th>Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($latestAttempts as $attempt)
                                    <tr>
                                        <td>
                                            <span class="participant-cell">
                                                <span class="table-avatar">{{ mb_strtoupper(mb_substr($attempt->participant->name, 0, 1)) }}</span>
                                                <span>{{ $attempt->participant->name }}<small>{{ $attempt->completed_at?->format('d M Y, H:i') }}</small></span>
                                            </span>
                                        </td>
                                        <td><strong>{{ number_format((float) $attempt->total_score, 2) }}</strong></td>
                                        <td><span class="table-level">{{ $attempt->maturity_level }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </section>
@endsection
