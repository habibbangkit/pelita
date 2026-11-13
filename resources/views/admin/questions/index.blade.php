@extends('layouts.app')

@section('title', 'Manajemen Pertanyaan')

@section('content')
    <section class="card management-page">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Manajemen instrumen</p>
                <h1>Pertanyaan asesmen</h1>
                <p class="muted">Atur urutan, isi, dan status pertanyaan dalam paket asesmen.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('admin.questions.index') }}" class="field assessment-selector">
            <label for="assessment_id">Paket asesmen</label>
            <select id="assessment_id" name="assessment_id" onchange="this.form.submit()">
                @foreach ($assessments as $assessment)
                    <option value="{{ $assessment->id }}" @selected($selectedAssessment?->id === $assessment->id)>
                        {{ $assessment->title }} — {{ $assessment->status }}
                    </option>
                @endforeach
            </select>
        </form>

        @if ($selectedAssessment)
            <div class="actions management-actions">
                <a class="button" href="{{ route('admin.questions.create', ['assessment_id' => $selectedAssessment->id]) }}">Tambah pertanyaan</a>
                @if ($selectedAssessment->status === 'published')
                    <form method="POST" action="{{ route('admin.assessments.unpublish', $selectedAssessment) }}">
                        @csrf
                        @method('PATCH')
                        <button class="button button-secondary" type="submit">Jadikan draft</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.assessments.publish', $selectedAssessment) }}">
                        @csrf
                        <button class="button button-secondary" type="submit">Validasi & publikasikan</button>
                    </form>
                @endif
            </div>

            <p class="table-scroll-hint">Geser tabel ke samping untuk melihat kolom lainnya.</p>
            <div class="table-wrap management-table-wrap">
                <table class="management-table question-table">
                    <thead>
                        <tr>
                            <th>Urutan</th>
                            <th>Pertanyaan</th>
                            <th>Dimensi</th>
                            <th>Tipe</th>
                            <th>Opsi</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($questions as $question)
                            <tr>
                                <td class="question-position">
                                    <strong>{{ $question->position }}</strong>
                                    @if ($question->is_active)
                                        <div class="move-actions">
                                            <form method="POST" action="{{ route('admin.questions.move', $question) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="direction" value="up">
                                                <button class="icon-button" type="submit" aria-label="Pindah ke atas" title="Pindah ke atas">↑</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.questions.move', $question) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="direction" value="down">
                                                <button class="icon-button" type="submit" aria-label="Pindah ke bawah" title="Pindah ke bawah">↓</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                                <td class="question-copy">{{ $question->question_text }}</td>
                                <td>{{ $question->dimension->name }}</td>
                                <td>{{ $question->type === 'multiple_choice' ? 'Pilihan ganda' : 'Skala Likert' }}</td>
                                <td>{{ $question->active_options_count }}</td>
                                <td><span class="status-tag {{ $question->is_active ? '' : 'status-tag-inactive' }}">{{ $question->is_active ? 'Aktif' : 'Diarsipkan' }}</span></td>
                                <td>
                                    @if ($question->is_active)
                                        <div class="table-actions">
                                            <a class="button button-secondary button-compact" href="{{ route('admin.questions.edit', $question) }}">Ubah</a>
                                            <form method="POST" action="{{ route('admin.questions.archive', $question) }}" onsubmit="return confirm('Arsipkan pertanyaan ini?')">
                                                @csrf
                                                @method('PATCH')
                                                <button class="button button-secondary button-compact" type="submit">Arsipkan</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="muted">Tersimpan</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="muted">Belum ada pertanyaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="muted">Belum ada paket asesmen. Jalankan seeder atau buat assessment melalui provisioning.</p>
        @endif
    </section>
@endsection
