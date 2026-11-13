<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Dimension;
use App\Models\PrivacySetting;
use App\Models\Question;
use App\Services\AdminActivityLogger;
use App\Services\AssessmentMutationGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssessmentManagementController extends Controller
{
    public function publish(
        Assessment $assessment,
        AssessmentMutationGuard $guard,
        AdminActivityLogger $activityLogger
    ): RedirectResponse {
        $guard->assertNoInProgressAttempts($assessment);

        if (! PrivacySetting::query()->find(1)?->isConfigured()) {
            throw ValidationException::withMessages([
                'privacy_settings' => 'Lengkapi pengaturan retensi dan kontak privasi sebelum memublikasikan asesmen.',
            ]);
        }

        $dimensions = Dimension::query()->where('is_active', true)->get();
        if ($dimensions->isEmpty() || abs($dimensions->sum(fn (Dimension $dimension) => (float) $dimension->weight) - 100) > 0.01) {
            throw ValidationException::withMessages([
                'assessment' => 'Bobot dimensi aktif harus berjumlah 100% sebelum asesmen dipublikasikan.',
            ]);
        }

        $questions = $assessment->questions()
            ->where('is_active', true)
            ->with(['dimension', 'options' => fn ($query) => $query->where('is_active', true)])
            ->get();

        if ($questions->isEmpty()) {
            throw ValidationException::withMessages([
                'assessment' => 'Tambahkan pertanyaan aktif terlebih dahulu.',
            ]);
        }

        $activeDimensionIds = $dimensions->modelKeys();
        foreach ($dimensions as $dimension) {
            if (! $questions->contains(fn (Question $question) => $question->dimension_id === $dimension->id)) {
                throw ValidationException::withMessages([
                    'assessment' => "Tambahkan pertanyaan aktif untuk dimensi {$dimension->name}.",
                ]);
            }
        }

        foreach ($questions as $question) {
            if (! in_array($question->dimension_id, $activeDimensionIds, true)) {
                throw ValidationException::withMessages([
                    'assessment' => "Pertanyaan {$question->position} memakai dimensi nonaktif.",
                ]);
            }

            if ($question->options->count() < 2) {
                throw ValidationException::withMessages([
                    'assessment' => "Pertanyaan {$question->position} harus memiliki sedikitnya dua opsi aktif.",
                ]);
            }

            if (! in_array($question->type, ['multiple_choice', 'likert'], true) || (float) $question->weight <= 0) {
                throw ValidationException::withMessages([
                    'assessment' => "Tipe atau bobot pertanyaan {$question->position} tidak valid.",
                ]);
            }

            if ($question->type === 'multiple_choice' && $question->options->where('is_correct', true)->count() !== 1) {
                throw ValidationException::withMessages([
                    'assessment' => "Pertanyaan {$question->position} harus memiliki tepat satu kunci jawaban.",
                ]);
            }

            if ($question->type === 'likert' && $question->options->contains(
                fn ($option) => ! in_array($option->score_value, [0, 25, 50, 75, 100], true)
            )) {
                throw ValidationException::withMessages([
                    'assessment' => "Setiap opsi Likert pada pertanyaan {$question->position} harus memiliki nilai 0, 25, 50, 75, atau 100.",
                ]);
            }
        }

        DB::transaction(function () use ($assessment, $activityLogger): void {
            Assessment::query()
                ->where('status', 'published')
                ->where('id', '<>', $assessment->id)
                ->update(['status' => 'draft']);

            $assessment->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
            $activityLogger->record('assessment.published', $assessment);
        });

        return redirect()
            ->route('admin.questions.index', ['assessment_id' => $assessment->id])
            ->with('status', 'Asesmen berhasil dipublikasikan.');
    }

    public function unpublish(
        Assessment $assessment,
        AssessmentMutationGuard $guard,
        AdminActivityLogger $activityLogger
    ): RedirectResponse {
        $guard->assertNoInProgressAttempts($assessment);
        DB::transaction(function () use ($assessment, $activityLogger): void {
            $assessment->update([
                'status' => 'draft',
                'published_at' => null,
            ]);
            $activityLogger->record('assessment.unpublished', $assessment);
        });

        return redirect()
            ->route('admin.questions.index', ['assessment_id' => $assessment->id])
            ->with('status', 'Asesmen dikembalikan ke status draft.');
    }
}
