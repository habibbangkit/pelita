@extends('layouts.app')

@section('title', 'Hasil asesmen')
@section('topbar_eyebrow', 'Pemetaan selesai')
@section('topbar_title', 'Ringkasan hasil Anda')

@section('content')
    <section class="results-page" aria-labelledby="results-heading">
        @php
            $recommendations = $attempt->recommendation_snapshot ?? [];
            if (isset($recommendations['title'])) {
                $recommendations = [$recommendations];
            }
            $questionSnapshots = $attempt->scoring_snapshot['questions'] ?? [];
        @endphp
        <div class="results-overview">
            <div class="results-intro">
                <span class="results-trophy" aria-hidden="true">
                    <svg viewBox="0 0 48 48"><path d="M14 8h20v8c0 9-4 14-10 14s-10-5-10-14V8Z"/><path d="M14 12H7v4c0 6 3 9 9 9M34 12h7v4c0 6-3 9-9 9M24 30v7m-9 4h18m-14-4h10"/></svg>
                </span>
                <div>
                    <p class="eyebrow">Asesmen selesai</p>
                    <h1 id="results-heading">Terima kasih, {{ $attempt->participant->name }}.</h1>
                    <p class="muted">Jawaban Anda tersimpan pada {{ $attempt->completed_at->format('d M Y, H:i') }}.</p>
                </div>
            </div>

            @if ($attempt->total_score !== null)
                <div class="score-highlight">
                    <span>Skor akhir</span>
                    <strong>{{ number_format((float) $attempt->total_score, 2) }}<small> / 100</small></strong>
                    <div class="progress" aria-hidden="true">
                        <span style="width: {{ min(100, max(0, (float) $attempt->total_score)) }}%"></span>
                    </div>
                    <span class="maturity-pill">Tingkat {{ $attempt->maturity_level }}</span>
                </div>
                <div class="results-download">
                    <a class="button button-secondary" href="{{ route('assessment.download', $attempt) }}" download>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3"/></svg>
                        Unduh laporan PDF
                    </a>
                    <span>Ringkasan skor, dimensi, dan rekomendasi pengembangan.</span>
                </div>
            @endif
        </div>

        @if ($attempt->result_email_status === 'sent')
            <div class="notice" role="status">Laporan PDF telah dikirim ke {{ $attempt->participant->email }}.</div>
        @elseif ($attempt->result_email_status === 'failed')
            <div class="error" role="alert">Laporan belum berhasil dikirim. Anda tetap dapat mengunduh PDF dari halaman ini.</div>
        @elseif ($attempt->result_email_status === 'skipped')
            <div class="notice" role="status">Email hasil tidak dikirim karena data peserta telah dianonimkan.</div>
        @elseif ($attempt->result_email_status === 'not_configured')
            <div class="error" role="alert">Pengiriman email belum aktif. Silakan unduh PDF atau hubungi pengelola.</div>
        @elseif ($attempt->result_email_status)
            <div class="notice" role="status">Laporan PDF sedang disiapkan dan akan dikirim ke {{ $attempt->participant->email }}.</div>
        @endif

        @if ($attempt->total_score !== null)
            <div class="results-grid">
                <section class="card dimensions-card" aria-labelledby="dimension-results-heading">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">Hasil pemetaan</p>
                            <h2 id="dimension-results-heading">Skor per dimensi</h2>
                        </div>
                        <span class="section-icon" aria-hidden="true">◈</span>
                    </div>
                    <div class="dimension-results">
                        @foreach ($attempt->dimensionScores as $dimensionScore)
                            <div class="dimension-result">
                                <div class="dimension-result-heading">
                                    <span>{{ $dimensionScore->dimension_name_snapshot }}</span>
                                    <strong>{{ number_format((float) $dimensionScore->score, 2) }}%</strong>
                                </div>
                                <div class="progress" role="img" aria-label="{{ $dimensionScore->dimension_name_snapshot }}: {{ number_format((float) $dimensionScore->score, 2) }} persen">
                                    <span style="width: {{ min(100, max(0, (float) $dimensionScore->score)) }}%"></span>
                                </div>
                                <span class="result-status {{ (float) $dimensionScore->score < 60 ? 'result-status-needs-work' : '' }}">
                                    {{ (float) $dimensionScore->score < 60 ? 'Perlu peningkatan' : 'Baik' }}
                                </span>
                            </div>
                        @endforeach
                        <p class="result-caption">Skor di bawah 60 ditandai sebagai prioritas pengembangan. Gunakan hasil ini sebagai gambaran awal, bukan keputusan tunggal tentang kemampuan Anda.</p>
                    </div>
                </section>

                <aside class="card recommendation-card" aria-labelledby="recommendation-heading">
                    <p class="eyebrow">PELITA Path</p>
                    <span class="recommendation-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 2.7 5.5L21 9.4l-4.5 4.4 1.1 6.2L12 17l-5.6 3 1.1-6.2L3 9.4l6.3-.9L12 3Z"/></svg></span>
                    <h2 id="recommendation-heading">Rekomendasi pembelajaran</h2>
                    @if (count($recommendations))
                        <p>Dimensi dengan skor di bawah 60 mendapat rekomendasi pengembangan.</p>
                        <div class="recommendation-list">
                            @foreach ($recommendations as $recommendation)
                                <article class="recommendation-item">
                                    <h3>{{ $recommendation['title'] }}</h3>
                                    <p>{{ $recommendation['description'] }}</p>
                                    @php
                                        $recommendationUrl = $recommendation['learning_url'] ?? null;
                                        $recommendationScheme = is_string($recommendationUrl) ? parse_url($recommendationUrl, PHP_URL_SCHEME) : null;
                                    @endphp
                                    @if (filter_var($recommendationUrl, FILTER_VALIDATE_URL) && in_array(strtolower((string) $recommendationScheme), ['http', 'https'], true))
                                        <a class="text-link recommendation-link" href="{{ $recommendationUrl }}" target="_blank" rel="noopener noreferrer">Buka materi lanjutan <span aria-hidden="true">↗</span></a>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p>Semua dimensi mencapai skor 60 atau lebih. Pertahankan kebiasaan belajar dan penggunaan AI yang bertanggung jawab.</p>
                    @endif
                </aside>
            </div>

            @if ($questionSnapshots)
                <section class="card answer-review" aria-labelledby="answer-review-heading">
                    <p class="eyebrow">Pembahasan</p>
                    <h2 id="answer-review-heading">Tinjau jawaban Anda</h2>
                    <div class="answer-review-list">
                        @foreach ($questionSnapshots as $index => $questionSnapshot)
                            <article class="answer-review-item">
                                <h3>{{ $index + 1 }}. {{ $questionSnapshot['question_text'] }}</h3>
                                <p><strong>Dimensi:</strong> {{ $questionSnapshot['dimension'] }} · <strong>Skor:</strong> {{ number_format((float) $questionSnapshot['score'], 2) }}</p>
                                <p><strong>Jawaban Anda:</strong> {{ $questionSnapshot['selected_option_text'] }}</p>
                                @if ($questionSnapshot['type'] === 'multiple_choice')
                                    <p><strong>{{ $questionSnapshot['option_is_correct'] ? 'Jawaban benar.' : 'Jawaban yang benar:' }}</strong> {{ $questionSnapshot['option_is_correct'] ? $questionSnapshot['selected_option_text'] : ($questionSnapshot['correct_option_text'] ?? 'Belum ditentukan') }}</p>
                                @else
                                    <p class="muted">Skor mengikuti rubrik kesiapan 0–100; tidak ada jawaban benar atau salah.</p>
                                @endif
                                @if (! empty($questionSnapshot['explanation']))
                                    <p class="muted">{{ $questionSnapshot['explanation'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @else
            <div class="card">
                <div class="notice">Hasil pengerjaan ini belum memiliki data penilaian. Silakan hubungi pengelola untuk tindak lanjut.</div>
            </div>
        @endif
    </section>
@endsection
