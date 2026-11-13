<?php

namespace App\Services;

use App\Models\AssessmentAttempt;
use Barryvdh\DomPDF\Facade\Pdf;

class AssessmentReportService
{
    public function data(AssessmentAttempt $attempt): array
    {
        $attempt->loadMissing(['participant', 'assessment', 'dimensionScores']);
        $recommendations = $attempt->recommendation_snapshot ?? [];
        if (isset($recommendations['title'])) {
            $recommendations = [$recommendations];
        }

        return [
            'attempt' => $attempt,
            'recommendations' => $recommendations,
            'summary' => $this->summary((float) $attempt->total_score),
            'reportId' => 'PLT-'.now()->format('Ymd').'-'.strtoupper(substr($attempt->uuid, 0, 6)),
        ];
    }

    public function summary(float $score): string
    {
        return match (true) {
            $score >= 80 => 'Hasil menunjukkan tingkat pemahaman dan kesiapan yang baik dalam mengadopsi teknologi AI.',
            $score >= 60 => 'Hasil menunjukkan fondasi pemahaman yang cukup baik, dengan beberapa aspek yang masih dapat dikembangkan.',
            default => 'Hasil menunjukkan bahwa peserta dapat memprioritaskan penguatan literasi dan kesiapan penggunaan AI.',
        };
    }

    public function pdf(AssessmentAttempt $attempt): string
    {
        return Pdf::loadView('guest.report-pdf', $this->data($attempt))
            ->setPaper('a4', 'portrait')
            ->output();
    }

    public function filename(AssessmentAttempt $attempt): string
    {
        return 'pelita-hasil-'.now()->format('Ymd').'-'.substr($attempt->uuid, 0, 8).'.pdf';
    }
}
