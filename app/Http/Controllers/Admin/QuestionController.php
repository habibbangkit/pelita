<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionUpsertRequest;
use App\Models\Assessment;
use App\Models\Dimension;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\AdminActivityLogger;
use App\Services\AssessmentMutationGuard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $assessments = Assessment::query()->orderByDesc('id')->get();
        $selectedAssessment = $request->filled('assessment_id')
            ? $assessments->firstWhere('id', (int) $request->query('assessment_id'))
            : $assessments->first();

        $questions = $selectedAssessment
            ? $selectedAssessment->questions()
                ->with('dimension')
                ->withCount(['options as active_options_count' => fn (Builder $query) => $query->where('is_active', true)])
                ->orderBy('position')
                ->get()
            : collect();

        return view('admin.questions.index', compact('assessments', 'selectedAssessment', 'questions'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $assessments = Assessment::query()->orderByDesc('id')->get();
        if ($assessments->isEmpty()) {
            return redirect()->route('admin.questions.index')
                ->withErrors(['assessment' => 'Buat data asesmen terlebih dahulu sebelum menambah pertanyaan.']);
        }

        $assessment = $assessments->firstWhere('id', (int) $request->query('assessment_id'))
            ?? $assessments->first();
        $dimensions = Dimension::query()->where('is_active', true)->orderBy('id')->get();
        $question = null;
        $nextPosition = (int) $assessment->questions()->max('position') + 1;
        $options = old('options', [
            ['option_text' => '', 'score_value' => null],
            ['option_text' => '', 'score_value' => null],
        ]);
        $correctOption = old('correct_option');

        return view('admin.questions.form', compact(
            'assessment',
            'assessments',
            'dimensions',
            'question',
            'options',
            'correctOption',
            'nextPosition'
        ));
    }

    public function store(
        QuestionUpsertRequest $request,
        AssessmentMutationGuard $guard,
        AdminActivityLogger $activityLogger
    ): RedirectResponse {
        $data = $request->validated();
        $assessment = Assessment::query()->findOrFail($data['assessment_id']);
        $guard->assertNoInProgressAttempts($assessment);
        $this->assertDraft($assessment);

        DB::transaction(function () use ($data, $assessment, $activityLogger): void {
            $question = $assessment->questions()->create($this->questionAttributes($data));
            $this->syncOptions($question, $data);
            $activityLogger->record('question.created', $question);
        });

        return redirect()
            ->route('admin.questions.index', ['assessment_id' => $assessment->id])
            ->with('status', 'Pertanyaan berhasil ditambahkan.');
    }

    public function edit(Question $question): View
    {
        abort_unless($question->is_active, 404);
        $question->load(['assessment', 'options' => fn ($query) => $query->where('is_active', true)->orderBy('position')]);
        $options = old('options', $question->options->map(fn (QuestionOption $option) => [
            'id' => $option->id,
            'option_text' => $option->option_text,
            'score_value' => $option->score_value,
        ])->all());
        $correctIndex = $question->options->search(fn (QuestionOption $option) => $option->is_correct);
        $correctOption = old('correct_option', $correctIndex === false ? null : $correctIndex);

        return view('admin.questions.form', [
            'assessment' => $question->assessment,
            'assessments' => collect([$question->assessment]),
            'dimensions' => Dimension::query()->where('is_active', true)->orderBy('id')->get(),
            'question' => $question,
            'options' => $options,
            'correctOption' => $correctOption,
            'nextPosition' => $question->position,
        ]);
    }

    public function update(
        QuestionUpsertRequest $request,
        Question $question,
        AssessmentMutationGuard $guard,
        AdminActivityLogger $activityLogger
    ): RedirectResponse {
        abort_unless($question->is_active, 404);
        $data = $request->validated();
        $assessment = $question->assessment;
        $guard->assertNoInProgressAttempts($assessment);
        $this->assertDraft($assessment);

        DB::transaction(function () use ($data, $question, $activityLogger): void {
            $question->update($this->questionAttributes($data));
            $this->syncOptions($question, $data);
            $activityLogger->record('question.updated', $question);
        });

        return redirect()
            ->route('admin.questions.index', ['assessment_id' => $assessment->id])
            ->with('status', 'Pertanyaan berhasil diperbarui.');
    }

    public function archive(
        Question $question,
        AssessmentMutationGuard $guard,
        AdminActivityLogger $activityLogger
    ): RedirectResponse
    {
        $assessment = $question->assessment;
        $guard->assertNoInProgressAttempts($assessment);
        $this->assertDraft($assessment);
        DB::transaction(function () use ($question, $activityLogger): void {
            $question->update(['is_active' => false]);
            $activityLogger->record('question.archived', $question);
        });

        return redirect()
            ->route('admin.questions.index', ['assessment_id' => $assessment->id])
            ->with('status', 'Pertanyaan diarsipkan; data jawaban lama tetap dipertahankan.');
    }

    public function move(
        Request $request,
        Question $question,
        AssessmentMutationGuard $guard,
        AdminActivityLogger $activityLogger
    ): RedirectResponse
    {
        abort_unless($question->is_active, 404);
        $data = $request->validate(['direction' => ['required', 'in:up,down']]);
        $assessment = $question->assessment;
        $guard->assertNoInProgressAttempts($assessment);
        $this->assertDraft($assessment);

        $neighborQuery = $assessment->questions()
            ->where('is_active', true)
            ->where('position', $data['direction'] === 'up' ? '<' : '>', $question->position);
        $neighborQuery->orderBy('position', $data['direction'] === 'up' ? 'desc' : 'asc');
        $neighbor = $neighborQuery->first();

        if (! $neighbor) {
            return back();
        }

        DB::transaction(function () use ($assessment, $question, $neighbor, $activityLogger): void {
            $temporaryPosition = (int) $assessment->questions()->max('position') + 1;
            $currentPosition = $question->position;
            $neighborPosition = $neighbor->position;

            $question->update(['position' => $temporaryPosition]);
            $neighbor->update(['position' => $currentPosition]);
            $question->update(['position' => $neighborPosition]);
            $activityLogger->record('question.reordered', $question);
        });

        return redirect()
            ->route('admin.questions.index', ['assessment_id' => $assessment->id])
            ->with('status', 'Urutan pertanyaan diperbarui.');
    }

    private function questionAttributes(array $data): array
    {
        return [
            'dimension_id' => $data['dimension_id'],
            'question_text' => trim($data['question_text']),
            'explanation' => isset($data['explanation']) ? trim($data['explanation']) : null,
            'type' => $data['type'],
            'weight' => $data['weight'],
            'position' => $data['position'],
            'is_required' => $data['is_required'],
            'is_active' => $data['is_active'],
        ];
    }

    private function assertDraft(Assessment $assessment): void
    {
        if ($assessment->status === 'published') {
            throw ValidationException::withMessages([
                'assessment' => 'Jadikan asesmen berstatus draft sebelum mengubah pertanyaannya.',
            ]);
        }
    }

    private function syncOptions(Question $question, array $data): void
    {
        $existingOptions = $question->options()->get();
        $temporaryPosition = (int) $question->options()->max('position') + $existingOptions->count() + 1;

        foreach ($existingOptions as $index => $option) {
            $option->update(['position' => $temporaryPosition + $index]);
        }

        $activeOptionIds = [];
        foreach (array_values($data['options']) as $index => $optionData) {
            $option = isset($optionData['id'])
                ? $question->options()->findOrFail($optionData['id'])
                : new QuestionOption;

            $option->fill([
                'option_text' => trim($optionData['option_text']),
                'score_value' => $data['type'] === 'likert' ? $optionData['score_value'] : null,
                'is_correct' => $data['type'] === 'multiple_choice'
                    ? (int) $data['correct_option'] === $index
                    : null,
                'is_active' => true,
                'position' => $index + 1,
            ]);
            $question->options()->save($option);
            $activeOptionIds[] = $option->id;
        }

        $question->options()
            ->whereNotIn('id', $activeOptionIds)
            ->update(['is_active' => false]);
    }
}
