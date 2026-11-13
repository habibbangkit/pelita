@extends('layouts.app')

@section('title', $question ? 'Ubah Pertanyaan' : 'Tambah Pertanyaan')

@section('content')
    <section class="card">
        <p class="eyebrow">Manajemen instrumen</p>
        <h1 style="font-size: 2rem">{{ $question ? 'Ubah pertanyaan' : 'Tambah pertanyaan' }}</h1>

        @if ($errors->any())
            <div class="error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ $question ? route('admin.questions.update', $question) : route('admin.questions.store') }}">
            @csrf
            @if ($question)
                @method('PUT')
                <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">
            @else
                <div class="field">
                    <label for="assessment_id">Paket asesmen</label>
                    <select id="assessment_id" name="assessment_id" required style="width: 100%; min-height: 46px; border: 1px solid #cbd5df; border-radius: 9px; padding: 10px 12px; font: inherit">
                        @foreach ($assessments as $assessmentOption)
                            <option value="{{ $assessmentOption->id }}" @selected((int) old('assessment_id', $assessment->id) === $assessmentOption->id)>{{ $assessmentOption->title }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="grid">
                <div class="field">
                    <label for="dimension_id">Dimensi</label>
                    <select id="dimension_id" name="dimension_id" required style="width: 100%; min-height: 46px; border: 1px solid #cbd5df; border-radius: 9px; padding: 10px 12px; font: inherit">
                        @foreach ($dimensions as $dimension)
                            <option value="{{ $dimension->id }}" @selected((int) old('dimension_id', $question?->dimension_id) === $dimension->id)>{{ $dimension->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="type">Tipe pertanyaan</label>
                    <select id="type" name="type" required style="width: 100%; min-height: 46px; border: 1px solid #cbd5df; border-radius: 9px; padding: 10px 12px; font: inherit">
                        <option value="multiple_choice" @selected(old('type', $question?->type ?? 'multiple_choice') === 'multiple_choice')>Pilihan ganda</option>
                        <option value="likert" @selected(old('type', $question?->type) === 'likert')>Skala Likert</option>
                    </select>
                </div>
                <div class="field">
                    <label for="weight">Bobot pertanyaan</label>
                    <input id="weight" name="weight" type="number" step="0.01" min="0.01" max="9999.99" value="{{ old('weight', $question?->weight ?? '1.00') }}" required>
                </div>
                <div class="field">
                    <label for="position">Urutan</label>
                    <input id="position" name="position" type="number" min="1" value="{{ old('position', $nextPosition) }}" required>
                </div>
            </div>

            <div class="field">
                <label for="question_text">Teks pertanyaan</label>
                <textarea id="question_text" name="question_text" rows="4" maxlength="5000" required style="width: 100%; border: 1px solid #cbd5df; border-radius: 9px; padding: 12px; font: inherit">{{ old('question_text', $question?->question_text) }}</textarea>
            </div>
            <div class="field">
                <label for="explanation">Pembahasan jawaban (ditampilkan setelah asesmen selesai)</label>
                <textarea id="explanation" name="explanation" rows="3" maxlength="4000" style="width: 100%; border: 1px solid #cbd5df; border-radius: 9px; padding: 12px; font: inherit">{{ old('explanation', $question?->explanation) }}</textarea>
            </div>

            <div class="grid">
                <div class="field">
                    <input type="hidden" name="is_required" value="0">
                    <label><input type="checkbox" name="is_required" value="1" @checked((bool) old('is_required', $question?->is_required ?? true))> Wajib dijawab</label>
                </div>
                <div class="field">
                    <input type="hidden" name="is_active" value="0">
                    <label><input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $question?->is_active ?? true))> Aktif</label>
                </div>
            </div>

            <h2>Opsi jawaban</h2>
            <p class="muted">Pilihan ganda harus memiliki tepat satu kunci jawaban. Nilai opsi Likert mengikuti rubrik 0, 25, 50, 75, atau 100.</p>
            <div id="options-list">
                @foreach ($options as $index => $option)
                    <div class="option-row" style="display: grid; grid-template-columns: minmax(0, 2fr) minmax(100px, 1fr) minmax(150px, 1fr) auto; gap: 10px; align-items: end; padding: 12px 0; border-top: 1px solid #e3eaf0">
                        <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option['id'] ?? '' }}">
                        <div>
                            <label>Label opsi</label>
                            <input type="text" name="options[{{ $index }}][option_text]" value="{{ $option['option_text'] ?? '' }}" maxlength="255" required>
                        </div>
                        <div>
                            <label>Nilai Likert</label>
                            <input class="score-value" type="number" name="options[{{ $index }}][score_value]" value="{{ $option['score_value'] ?? '' }}" min="0" max="100" step="25">
                        </div>
                        <label class="correct-control" style="margin: 0">
                            <input type="radio" name="correct_option" value="{{ $index }}" @checked((string) $correctOption === (string) $index)>
                            Kunci jawaban
                        </label>
                        <button class="remove-option button button-secondary" type="button" aria-label="Hapus opsi">Hapus</button>
                    </div>
                @endforeach
            </div>
            <button id="add-option" class="button button-secondary" type="button">Tambah opsi</button>

            <div class="actions">
                <a class="button button-secondary" href="{{ route('admin.questions.index', ['assessment_id' => $assessment->id]) }}">Batal</a>
                <button class="button" type="submit">Simpan pertanyaan</button>
            </div>
        </form>
    </section>

    <script>
        const typeField = document.getElementById('type');
        const optionsList = document.getElementById('options-list');

        function refreshOptions() {
            const rows = [...optionsList.querySelectorAll('.option-row')];
            rows.forEach((row, index) => {
                row.querySelectorAll('input[name^="options["]').forEach((input) => {
                    input.name = input.name.replace(/options\[\d+\]/, `options[${index}]`);
                });
                const correct = row.querySelector('.correct-control input');
                correct.value = index;
                const showCorrect = typeField.value === 'multiple_choice';
                row.querySelector('.correct-control').hidden = !showCorrect;
                correct.disabled = !showCorrect;
                const score = row.querySelector('.score-value');
                score.required = typeField.value === 'likert';
                score.disabled = typeField.value !== 'likert';
                row.querySelector('.remove-option').disabled = rows.length <= 2;
            });
        }

        document.getElementById('add-option').addEventListener('click', () => {
            const index = optionsList.querySelectorAll('.option-row').length;
            const row = document.createElement('div');
            row.className = 'option-row';
            row.style.cssText = 'display:grid;grid-template-columns:minmax(0,2fr) minmax(100px,1fr) minmax(150px,1fr) auto;gap:10px;align-items:end;padding:12px 0;border-top:1px solid #e3eaf0';
            row.innerHTML = `<input type="hidden" name="options[${index}][id]" value=""><div><label>Label opsi</label><input type="text" name="options[${index}][option_text]" maxlength="255" required></div><div><label>Nilai Likert</label><input class="score-value" type="number" name="options[${index}][score_value]" min="0" max="100" step="25"></div><label class="correct-control" style="margin:0"><input type="radio" name="correct_option" value="${index}"> Kunci jawaban</label><button class="remove-option button button-secondary" type="button">Hapus</button>`;
            optionsList.append(row);
            refreshOptions();
        });

        optionsList.addEventListener('click', (event) => {
            if (event.target.closest('.remove-option')) {
                event.target.closest('.option-row').remove();
                refreshOptions();
            }
        });
        typeField.addEventListener('change', refreshOptions);
        refreshOptions();
    </script>
@endsection
