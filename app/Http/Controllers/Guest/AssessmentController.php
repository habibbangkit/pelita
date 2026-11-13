<?php

namespace App\Http\Controllers\Guest;

use App\Exceptions\InvalidScoringConfiguration;
use App\Jobs\SendAssessmentResultEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartAssessmentRequest;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AttemptDimensionScore;
use App\Models\Participant;
use App\Models\PrivacySetting;
use App\Services\AssessmentReportService;
use App\Services\ScoringService;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AssessmentController extends Controller
{
    public function start(StartAssessmentRequest $request): RedirectResponse
    {
        $assessment = Assessment::query()
            ->where('status', 'published')
            ->whereHas('questions', fn ($query) => $query->where('is_active', true))
            ->whereDoesntHave('questions', fn ($query) => $query
                ->where('is_active', true)
                ->whereDoesntHave('options', fn ($options) => $options->where('is_active', true)))
            ->latest('published_at')
            ->first();

        if (! $assessment) {
            return back()
                ->withInput()
                ->withErrors(['assessment' => 'Asesmen belum tersedia. Silakan coba kembali nanti.']);
        }

        $privacySettings = PrivacySetting::query()->find(1);
        if (! $privacySettings?->isConfigured()) {
            return back()
                ->withInput()
                ->withErrors(['assessment' => 'Pemberitahuan privasi belum dikonfigurasi. Asesmen belum dapat dimulai.']);
        }

        $attempt = DB::transaction(function () use ($request, $assessment) {
            $name = trim($request->validated('name'));
            $email = Str::lower(trim($request->validated('email')));
            $participant = Participant::query()
                ->where('email', $email)
                ->orderBy('id')
                ->first();

            if (! $participant) {
                $participant = Participant::create([
                    'name' => $name,
                    'email' => $email,
                ]);
            } elseif (! $participant->is_anonymized && $participant->name !== $name) {
                $participant->update(['name' => $name]);
            }

            return AssessmentAttempt::create([
                'uuid' => (string) Str::uuid(),
                'participant_id' => $participant->id,
                'assessment_id' => $assessment->id,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        });

        return redirect()->route('assessment.show', $attempt);
    }

    public function show(AssessmentAttempt $attempt, Request $request): View|RedirectResponse
    {
        if ($attempt->status !== 'in_progress') {
            return redirect()->route('assessment.complete', $attempt);
        }

        $questions = $attempt->assessment->questions()
            ->where('is_active', true)
            ->with([
                'dimension',
                'options' => fn ($query) => $query->where('is_active', true)->orderBy('position'),
            ])
            ->orderBy('position')
            ->get();

        if ($questions->isEmpty()) {
            abort(404);
        }

        $index = max(0, min((int) $request->query('question', 0), $questions->count() - 1));
        $question = $questions->get($index);
        $answer = $attempt->answers()->where('question_id', $question->id)->first();

        return view('guest.assessment', [
            'attempt' => $attempt,
            'question' => $question,
            'questionIndex' => $index,
            'questionCount' => $questions->count(),
            'savedOptionId' => $answer?->question_option_id,
        ]);
    }

    public function saveAnswer(Request $request, AssessmentAttempt $attempt): RedirectResponse
    {
        if ($attempt->status !== 'in_progress') {
            abort(404);
        }

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'question_option_id' => ['nullable', 'integer', 'exists:question_options,id'],
            'action' => ['required', 'in:previous,next,finish'],
        ]);

        $questions = $attempt->assessment->questions()
            ->where('is_active', true)
            ->orderBy('position')
            ->get();
        $question = $questions
            ->firstWhere('id', (int) $data['question_id']);

        if (! $question) {
            abort(404);
        }

        $questionIndex = $questions->search(
            fn ($candidate) => $candidate->id === $question->id
        );

        if ($questionIndex === false) {
            abort(404);
        }

        if (! empty($data['question_option_id'] ?? null)) {
            $optionBelongsToQuestion = $question->options()
                ->where('is_active', true)
                ->whereKey($data['question_option_id'])
                ->exists();

            if (! $optionBelongsToQuestion) {
                throw ValidationException::withMessages([
                    'question_option_id' => 'Pilihan jawaban tidak sesuai dengan pertanyaan.',
                ]);
            }

            $attempt->answers()->updateOrCreate(
                ['question_id' => $question->id],
                ['question_option_id' => $data['question_option_id']]
            );
        }

        if ($data['action'] === 'finish') {
            $answeredQuestionIds = $attempt->answers()->pluck('question_id');
            $unanswered = $questions->first(
                fn ($candidate) => $candidate->is_required && ! $answeredQuestionIds->contains($candidate->id)
            );

            if ($unanswered) {
                $unansweredIndex = $questions->search(
                    fn ($candidate) => $candidate->id === $unanswered->id
                );

                return redirect()
                    ->route('assessment.show', ['attempt' => $attempt, 'question' => $unansweredIndex])
                    ->withErrors(['assessment' => 'Jawab semua pertanyaan wajib sebelum menyelesaikan asesmen.']);
            }

            try {
                DB::transaction(function () use ($attempt) {
                    $result = app(ScoringService::class)->calculate($attempt);

                    foreach ($result['dimension_scores'] as $score) {
                        AttemptDimensionScore::create([
                            'attempt_id' => $attempt->id,
                            'dimension_id' => $score['dimension_id'],
                            'dimension_name_snapshot' => $score['dimension_name'],
                            'score' => $score['score'],
                            'weight_snapshot' => $score['weight'],
                        ]);
                    }

                    $attempt->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'total_score' => $result['total_score'],
                        'maturity_level' => $result['maturity_level'],
                        'scoring_snapshot' => $result['snapshot'],
                        'recommendation_snapshot' => $result['recommendations'],
                        'result_email_status' => 'pending',
                    ]);
                });
            } catch (InvalidScoringConfiguration $exception) {
                report($exception);

                return redirect()
                    ->route('assessment.show', ['attempt' => $attempt, 'question' => $questionIndex])
                    ->withErrors(['assessment' => 'Asesmen belum dapat dinilai. Silakan hubungi pengelola.']);
            }

            try {
                SendAssessmentResultEmail::dispatch($attempt->id);
            } catch (Throwable $exception) {
                report($exception);
                $attempt->update(['result_email_status' => 'failed']);
            }

            return redirect()->route('assessment.complete', $attempt);
        }

        $nextIndex = $data['action'] === 'previous'
            ? max(0, $questionIndex - 1)
            : min($questionIndex + 1, $questions->count() - 1);

        return redirect()->route('assessment.show', ['attempt' => $attempt, 'question' => $nextIndex]);
    }

    public function complete(AssessmentAttempt $attempt): View|RedirectResponse
    {
        if ($attempt->status !== 'completed') {
            return redirect()->route('assessment.show', $attempt);
        }

        $attempt->load(['dimensionScores', 'participant']);

        return view('guest.complete', compact('attempt'));
    }

    public function download(AssessmentAttempt $attempt, AssessmentReportService $reportService): Response
    {
        abort_unless($attempt->status === 'completed' && $attempt->total_score !== null, 404);

        return response($reportService->pdf($attempt), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$reportService->filename($attempt).'"',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
