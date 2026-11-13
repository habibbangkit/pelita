<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use Illuminate\Validation\ValidationException;

class AssessmentMutationGuard
{
    public function assertNoInProgressAttempts(Assessment $assessment): void
    {
        if ($assessment->attempts()->where('status', 'in_progress')->exists()) {
            throw ValidationException::withMessages([
                'assessment' => 'Konten asesmen tidak dapat diubah saat ada peserta yang sedang mengerjakan.',
            ]);
        }
    }

    public function assertNoInProgressAttemptsAnywhere(): void
    {
        if (AssessmentAttempt::query()->where('status', 'in_progress')->exists()) {
            throw ValidationException::withMessages([
                'assessment' => 'Pengaturan tidak dapat diubah saat ada peserta yang sedang mengerjakan asesmen.',
            ]);
        }
    }
}
