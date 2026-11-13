@extends('layouts.app')

@section('title', 'Detail Hasil Peserta')

@section('content')
    <div class="participant-detail">
        <section class="card">
            <div class="page-heading">
                <div>
                    <p class="eyebrow">Detail peserta</p>
                    <h1>{{ $attempt->participant->name }}</h1>
                    <p class="muted">{{ $attempt->participant->email }} · {{ $attempt->assessment->title }}</p>
                </div>
                <a class="button button-secondary" href="{{ route('assessment.download', $attempt) }}">Unduh laporan PDF</a>
            </div>

            <div class="results-overview participant-results-overview">
                <div class="results-intro">
                    <span class="results-trophy" aria-hidden="true">
                        <svg viewBox="0 0 48 48"><path d="M14 8h20v8c0 9-4 14-10 14s-10-5-10-14V8Z"/><path d="M14 12H7v4c0 6 3 9 9 9M34 12h7v4c0 6-3 9-9 9M24 30v7m-9 4h18m-14-4h10"/></svg>
                    </span>
                    <div>
                        <p class="eyebrow">Ringkasan hasil</p>
                        <p class="muted">Asesmen selesai {{ $attempt->completed_at->format('d M Y, H:i') }}.</p>
                    </div>
                </div>
                <div class="score-highlight">
                    <span>Skor akhir</span>
                    <strong>{{ number_format((float) $attempt->total_score, 2) }}<small> / 100</small></strong>
                    <span class="maturity-pill">Tingkat {{ $attempt->maturity_level }}</span>
                </div>
            </div>

            <div class="participant-summary-text">
                <strong>Kesimpulan</strong>
                <p>{{ $summary }} Gunakan hasil ini sebagai gambaran awal untuk merencanakan pengembangan diri.</p>
            </div>

            <div class="notice participant-email-status">
                <strong>Status pengiriman hasil</strong>
                <span class="status-tag {{ $emailStatusClass }}">{{ $emailStatus }}</span>
            </div>
        </section>

        <section class="card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Hasil pemetaan</p>
                    <h2>Skor per dimensi</h2>
                </div>
            </div>
            <div class="participant-dimension-grid">
                @foreach ($attempt->dimensionScores as $dimensionScore)
                    @php
                        $needsWork = (float) $dimensionScore->score < 60;
                    @endphp
                    <article class="participant-dimension-card">
                        <div class="dimension-result-heading">
                            <span>{{ $dimensionScore->dimension_name_snapshot }}</span>
                            <strong>{{ number_format((float) $dimensionScore->score, 2) }}%</strong>
                        </div>
                        <div class="progress" role="img" aria-label="{{ $dimensionScore->dimension_name_snapshot }}: {{ number_format((float) $dimensionScore->score, 2) }} persen">
                            <span style="width: {{ min(100, max(0, (float) $dimensionScore->score)) }}%"></span>
                        </div>
                        <div class="participant-dimension-meta">
                            <span class="result-status {{ $needsWork ? 'result-status-needs-work' : '' }}">{{ $needsWork ? 'Perlu peningkatan' : 'Baik' }}</span>
                            <span class="muted">Bobot {{ number_format((float) $dimensionScore->weight_snapshot, 2) }}%</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Langkah berikutnya</p>
                    <h2>Rekomendasi pengembangan</h2>
                </div>
            </div>
            @if (count($recommendations))
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
                                <a class="text-link" href="{{ $recommendationUrl }}" target="_blank" rel="noopener noreferrer">Buka materi lanjutan ↗</a>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <p class="muted">Semua dimensi mencapai skor 60 atau lebih. Pertahankan kebiasaan belajar dan penggunaan AI yang bertanggung jawab.</p>
            @endif
        </section>

        <section class="card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Pengerjaan terdahulu</p>
                    <h2>Riwayat pengerjaan peserta</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu selesai</th>
                            <th>Asesmen</th>
                            <th>Skor</th>
                            <th>Level</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $historyAttempt)
                            <tr>
                                <td>{{ $historyAttempt->completed_at->format('d M Y, H:i') }}</td>
                                <td>{{ $historyAttempt->assessment->title }}</td>
                                <td>{{ number_format((float) $historyAttempt->total_score, 2) }}</td>
                                <td>{{ $historyAttempt->maturity_level }}</td>
                                <td><a href="{{ route('admin.participants.show', $historyAttempt) }}">Lihat</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Rincian respons</p>
                    <h2>Jawaban peserta</h2>
                </div>
            </div>
            <div class="answer-review-list">
                @foreach ($attempt->answers->sortBy(fn ($answer) => $answer->question->position) as $answer)
                    @php($questionSnapshot = $questionSnapshots->get($answer->question_id, []))
                    <article class="answer-review-item">
                        <h3>{{ $loop->iteration }}. {{ $answer->question_text_snapshot ?? ($questionSnapshot['question_text'] ?? $answer->question->question_text) }}</h3>
                        <p><strong>Dimensi:</strong> {{ $answer->dimension_name_snapshot ?? ($questionSnapshot['dimension'] ?? $answer->question->dimension->name) }}</p>
                        <p><strong>Jawaban peserta:</strong> {{ $answer->option_text_snapshot ?? ($questionSnapshot['selected_option_text'] ?? $answer->option?->option_text ?? 'Tidak tersedia') }}</p>
                        @if (($questionSnapshot['type'] ?? null) === 'multiple_choice')
                            <p><strong>{{ ($questionSnapshot['option_is_correct'] ?? false) ? 'Jawaban benar.' : 'Jawaban yang benar:' }}</strong> {{ ($questionSnapshot['option_is_correct'] ?? false) ? ($questionSnapshot['selected_option_text'] ?? '') : ($questionSnapshot['correct_option_text'] ?? 'Belum ditentukan') }}</p>
                        @endif
                        <p><strong>Skor jawaban:</strong> {{ number_format((float) ($answer->score ?? ($questionSnapshot['score'] ?? 0)), 2) }}</p>
                        @if (! empty($questionSnapshot['explanation']))
                            <p class="muted">{{ $questionSnapshot['explanation'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <div>
            <a class="button button-secondary" href="{{ route('admin.participants.index') }}">Kembali ke daftar peserta</a>
        </div>
    </div>
@endsection
