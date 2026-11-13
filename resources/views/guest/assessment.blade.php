@extends('layouts.app')

@section('title', 'Asesmen')
@section('topbar_eyebrow', 'Asesmen aktif')
@section('topbar_title', $attempt->assessment->title)

@section('content')
    @php($progress = round((($questionIndex + 1) / $questionCount) * 100))

    <div class="assessment-page">
        <section class="card assessment-main" aria-labelledby="question-heading">
            <div class="assessment-title-row">
                <div>
                    <p class="eyebrow">Pemetaan Literasi AI</p>
                    <h1 id="question-heading">Satu langkah lebih dekat.</h1>
                    <p class="muted assessment-greeting">Halo, {{ $attempt->participant->name }}. Jawab sesuai pengalaman Anda.</p>
                </div>
                @if ($question->is_required)
                    <span class="required-pill"><span></span> Wajib dijawab</span>
                @else
                    <span class="optional-pill">Opsional</span>
                @endif
            </div>

            <div class="question-progress">
                <div class="question-progress-label">
                    <span>Pertanyaan {{ $questionIndex + 1 }} <span class="muted">/ {{ $questionCount }}</span></span>
                    <strong>{{ $progress }}%</strong>
                </div>
                <div class="progress" role="progressbar" aria-label="Progres asesmen" aria-valuenow="{{ $questionIndex + 1 }}" aria-valuemin="1" aria-valuemax="{{ $questionCount }}">
                    <span style="width: {{ $progress }}%"></span>
                </div>
            </div>

            @if ($errors->any())
                <div class="error" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('assessment.answer', $attempt) }}">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">
                <fieldset class="answer-fieldset">
                    <legend class="question-text">{{ $question->question_text }}</legend>
                    @if ($question->dimension)
                        <span class="dimension-tag">Dimensi: {{ $question->dimension->name }}</span>
                    @endif
                    <div class="answer-options">
                        @foreach ($question->options as $option)
                            <label class="answer-option {{ (string) $savedOptionId === (string) $option->id ? 'is-selected' : '' }}">
                                <input type="radio" name="question_option_id" value="{{ $option->id }}" @checked((string) $savedOptionId === (string) $option->id)>
                                <span class="radio-indicator" aria-hidden="true"></span>
                                <span>{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="assessment-actions">
                    @if ($questionIndex > 0)
                        <button class="button button-secondary" type="submit" name="action" value="previous">
                            <span aria-hidden="true">←</span> Sebelumnya
                        </button>
                    @else
                        <span class="action-spacer"></span>
                    @endif
                    @if ($questionIndex < $questionCount - 1)
                        <button class="button" type="submit" name="action" value="next">Selanjutnya <span aria-hidden="true">→</span></button>
                    @else
                        <button class="button" type="submit" name="action" value="finish">Selesaikan asesmen <span aria-hidden="true">✓</span></button>
                    @endif
                </div>
            </form>
        </section>

        <aside class="card assessment-side" aria-label="Ringkasan progres">
            <p class="eyebrow">Progres Anda</p>
            <div class="progress-ring" style="--progress: {{ $progress }}%" role="img" aria-label="Progres {{ $progress }} persen">
                <div class="progress-ring-center">
                    <strong>{{ $progress }}%</strong>
                    <span>selesai</span>
                </div>
            </div>
            <p class="progress-count"><strong>{{ $questionIndex + 1 }}</strong> dari {{ $questionCount }} pertanyaan</p>

            <div class="side-divider"></div>
            <h2>Dimensi saat ini</h2>
            <div class="current-dimension">
                <span class="dimension-dot"></span>
                <span>{{ $question->dimension?->name ?? 'Pemetaan umum' }}</span>
            </div>
            <div class="side-note">
                <span class="side-note-icon" aria-hidden="true">✦</span>
                <span>Jawaban Anda tersimpan saat melanjutkan. Tidak ada jawaban benar atau salah untuk pengalaman pribadi.</span>
            </div>
        </aside>
    </div>
@endsection
