<?php

namespace App\Services;

use App\Models\AssessmentAttempt;
use App\Models\AttemptDimensionScore;
use App\Models\MaturityLevel;
use Carbon\Carbon;

class AdminAssessmentSummaryService
{
    public function data(): array
    {
        $completed = AssessmentAttempt::query()
            ->where('status', 'completed')
            ->whereNotNull('total_score');

        $totalAttempts = (clone $completed)->count();
        $totalParticipants = (clone $completed)
            ->distinct()
            ->count('participant_id');
        $averageScore = (clone $completed)->avg('total_score');
        $period = (clone $completed)
            ->selectRaw('MIN(completed_at) as first_completed_at, MAX(completed_at) as last_completed_at')
            ->first();

        $counts = (clone $completed)
            ->selectRaw('maturity_level, COUNT(*) as aggregate')
            ->groupBy('maturity_level')
            ->pluck('aggregate', 'maturity_level');

        $palette = ['Aware' => '#d46291', 'Adopter' => '#e1a33f', 'Transformer' => '#6748cf'];
        $offset = 0.0;
        $circumference = 276.46;
        $levels = MaturityLevel::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get()
            ->map(function (MaturityLevel $level, int $index) use ($counts, $totalAttempts, $palette, &$offset, $circumference): array {
                $count = (int) $counts->get($level->name, 0);
                $percentage = $totalAttempts > 0 ? $count / $totalAttempts * 100 : 0;
                $dashLength = $circumference * $percentage / 100;
                $color = $palette[$level->name] ?? ['#8066d8', '#49a58a', '#db8e50'][$index % 3];
                $result = [
                    'name' => $level->name,
                    'count' => $count,
                    'percentage' => $percentage,
                    'color' => $color,
                    'dashLength' => $dashLength,
                    'dashOffset' => $offset,
                ];
                $offset += $dashLength;

                return $result;
            });

        $dominantLevel = $levels->sortByDesc('count')->first();
        if ($totalAttempts === 0 || ! $dominantLevel || $dominantLevel['count'] === 0) {
            $dominantLevel = null;
        }

        $stops = [];
        $gradientOffset = 0.0;
        foreach ($levels as $level) {
            if ($level['percentage'] <= 0) {
                continue;
            }

            $end = $gradientOffset + $level['percentage'];
            $stops[] = $level['color'].' '.number_format($gradientOffset, 2, '.', '').'% '.number_format($end, 2, '.', '').'%';
            $gradientOffset = $end;
        }

        $dimensionAverages = AttemptDimensionScore::query()
            ->whereHas('attempt', fn ($query) => $query
                ->where('status', 'completed')
                ->whereNotNull('total_score'))
            ->selectRaw('dimension_id, dimension_name_snapshot, AVG(score) as average_score')
            ->groupBy('dimension_id', 'dimension_name_snapshot')
            ->orderBy('dimension_id')
            ->get();

        $firstDate = $period?->first_completed_at
            ? Carbon::parse($period->first_completed_at)->locale('id')->translatedFormat('d F Y')
            : null;
        $lastDate = $period?->last_completed_at
            ? Carbon::parse($period->last_completed_at)->locale('id')->translatedFormat('d F Y')
            : null;

        return [
            'totalParticipants' => $totalParticipants,
            'totalAttempts' => $totalAttempts,
            'averageScore' => $averageScore !== null ? number_format((float) $averageScore, 2) : null,
            'levels' => $levels,
            'dominantLevel' => $dominantLevel,
            'circumference' => $circumference,
            'distributionGradient' => $stops
                ? 'conic-gradient('.implode(', ', $stops).')'
                : 'conic-gradient(#e9e5f3 0% 100%)',
            'dimensionAverages' => $dimensionAverages,
            'latestAttempts' => (clone $completed)
                ->with('participant', 'assessment')
                ->latest('completed_at')
                ->latest('id')
                ->limit(5)
                ->get(),
            'periodLabel' => match (true) {
                $firstDate === null => 'Belum ada data asesmen',
                $firstDate === $lastDate => $firstDate,
                default => $firstDate.' – '.$lastDate,
            },
            'generatedAt' => now(),
        ];
    }
}
