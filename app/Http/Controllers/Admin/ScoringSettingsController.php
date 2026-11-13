<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScoringSettingsRequest;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Dimension;
use App\Models\MaturityLevel;
use App\Services\AdminActivityLogger;
use App\Services\AssessmentMutationGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScoringSettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.scoring-settings', [
            'dimensions' => Dimension::query()->orderBy('id')->get(),
            'levels' => MaturityLevel::query()->orderBy('position')->get(),
        ]);
    }

    public function update(
        ScoringSettingsRequest $request,
        AssessmentMutationGuard $guard,
        AdminActivityLogger $activityLogger
    ): RedirectResponse {
        $guard->assertNoInProgressAttemptsAnywhere();
        $data = $request->validated();

        $this->validatePublishedAssessmentCompatibility($data['dimensions']);

        DB::transaction(function () use ($data, $activityLogger): void {
            foreach (Dimension::query()->get() as $dimension) {
                $values = $data['dimensions'][$dimension->id] ?? $data['dimensions'][(string) $dimension->id];
                $dimension->update([
                    'weight' => $values['weight'],
                    'is_active' => filter_var($values['is_active'], FILTER_VALIDATE_BOOLEAN),
                ]);
            }

            foreach (MaturityLevel::query()->get() as $level) {
                $values = $data['levels'][$level->id] ?? $data['levels'][(string) $level->id];
                $level->update([
                    'minimum_score' => $values['minimum_score'],
                    'maximum_score' => $values['maximum_score'],
                ]);
            }

            $activityLogger->record('scoring.updated');
        });

        return redirect()->route('admin.scoring-settings.index')->with('status', 'Pengaturan penilaian berhasil disimpan.');
    }

    private function validatePublishedAssessmentCompatibility(array $submittedDimensions): void
    {
        $assessment = Assessment::query()->where('status', 'published')->first();
        if (! $assessment) {
            return;
        }

        $activeIds = collect($submittedDimensions)
            ->filter(fn (array $values) => filter_var($values['is_active'], FILTER_VALIDATE_BOOLEAN))
            ->keys()
            ->map(fn ($id) => (int) $id);
        $questionDimensionIds = $assessment->questions()
            ->where('is_active', true)
            ->distinct()
            ->pluck('dimension_id');

        if ($activeIds->diff($questionDimensionIds)->isNotEmpty()
            || $questionDimensionIds->diff($activeIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'dimensions' => 'Dimensi aktif harus sama dengan dimensi yang memiliki pertanyaan pada asesmen terpublikasi.',
            ]);
        }

        if (AssessmentAttempt::query()->where('assessment_id', $assessment->id)->where('status', 'in_progress')->exists()) {
            throw ValidationException::withMessages([
                'dimensions' => 'Pengaturan tidak dapat diubah saat ada peserta pada asesmen terpublikasi.',
            ]);
        }
    }
}
