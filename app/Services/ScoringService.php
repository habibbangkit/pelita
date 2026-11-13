<?php

namespace App\Services;

use App\Exceptions\InvalidScoringConfiguration;
use App\Models\AssessmentAttempt;
use App\Models\Dimension;
use App\Models\MaturityLevel;
use App\Models\Recommendation;
use Illuminate\Database\Eloquent\Collection;

class ScoringService
{
    public function calculate(AssessmentAttempt $attempt): array
    {
        $attempt->loadMissing([
            'assessment.questions.dimension',
            'assessment.questions.options',
            'answers.question.dimension',
            'answers.option',
        ]);

        $dimensions = Dimension::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($dimensions->isEmpty()) {
            throw new InvalidScoringConfiguration('Tidak ada dimensi penilaian aktif.');
        }

        $dimensionWeightTotal = $dimensions->sum(fn (Dimension $dimension) => (float) $dimension->weight);
        if (abs($dimensionWeightTotal - 100) > 0.01) {
            throw new InvalidScoringConfiguration('Total bobot dimensi aktif harus 100%.');
        }

        $questions = $attempt->assessment->questions
            ->where('is_active', true)
            ->values();
        if ($questions->isEmpty()) {
            throw new InvalidScoringConfiguration('Asesmen tidak memiliki pertanyaan aktif.');
        }

        $activeDimensionIds = $dimensions->modelKeys();
        if ($questions->contains(fn ($question) => ! in_array($question->dimension_id, $activeDimensionIds, true))) {
            throw new InvalidScoringConfiguration('Setiap pertanyaan aktif harus terkait dengan dimensi aktif.');
        }

        $answers = $attempt->answers->keyBy('question_id');
        $missingRequired = $questions->contains(
            fn ($question) => $question->is_required && ! $answers->has($question->id)
        );
        if ($missingRequired) {
            throw new InvalidScoringConfiguration('Semua pertanyaan wajib harus dijawab sebelum penilaian.');
        }

        $questionSnapshots = [];
        $dimensionScores = [];

        foreach ($dimensions as $dimension) {
            $dimensionQuestions = $questions->where('dimension_id', $dimension->id);
            if ($dimensionQuestions->isEmpty()) {
                throw new InvalidScoringConfiguration("Dimensi {$dimension->name} belum memiliki pertanyaan aktif.");
            }

            $weightedScore = 0.0;
            $questionWeightTotal = 0.0;

            foreach ($dimensionQuestions as $question) {
                $answer = $answers->get($question->id);
                if (! $answer) {
                    continue;
                }

                if ((float) $question->weight <= 0) {
                    throw new InvalidScoringConfiguration("Bobot pertanyaan {$question->id} harus lebih besar dari 0.");
                }

                $option = $answer->option;
                if (! $option || ! $option->is_active || $option->question_id !== $question->id) {
                    throw new InvalidScoringConfiguration("Pilihan jawaban untuk pertanyaan {$question->id} tidak valid.");
                }

                $score = $this->scoreAnswer($question->type, $option->is_correct, $option->score_value, $question->id);
                $weight = (float) $question->weight;

                $answer->score = $score;
                $answer->question_text_snapshot = $question->question_text;
                $answer->dimension_name_snapshot = $dimension->name;
                $answer->option_text_snapshot = $option->option_text;
                $answer->save();
                $correctOption = $question->type === 'multiple_choice'
                    ? $question->options->first(fn ($candidate) => $candidate->is_active && $candidate->is_correct)
                    : null;

                $weightedScore += $score * $weight;
                $questionWeightTotal += $weight;
                $questionSnapshots[] = [
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'dimension' => $dimension->name,
                    'type' => $question->type,
                    'weight' => $weight,
                    'selected_option_id' => $option->id,
                    'selected_option_text' => $option->option_text,
                    'option_is_correct' => $option->is_correct,
                    'option_score_value' => $option->score_value,
                    'correct_option_text' => $correctOption?->option_text,
                    'explanation' => $question->explanation,
                    'score' => $score,
                ];
            }

            if ($questionWeightTotal <= 0) {
                throw new InvalidScoringConfiguration("Dimensi {$dimension->name} tidak memiliki jawaban yang dapat dinilai.");
            }

            $rawDimensionScore = $weightedScore / $questionWeightTotal;
            $dimensionScores[] = [
                'dimension_id' => $dimension->id,
                'dimension_name' => $dimension->name,
                'score' => round($rawDimensionScore, 2),
                'raw_score' => $rawDimensionScore,
                'weight' => (float) $dimension->weight,
            ];
        }

        $totalScore = round(
            collect($dimensionScores)->sum(fn (array $score) => $score['raw_score'] * $score['weight']) / 100,
            2
        );
        $maturityLevels = MaturityLevel::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get();
        $this->validateMaturityLevels($maturityLevels);
        $maturityLevel = $this->maturityLevel($totalScore, $maturityLevels);
        $recommendations = collect($dimensionScores)
            ->filter(fn (array $score) => $score['score'] < 60)
            ->sort(fn (array $left, array $right) => [$left['score'], $left['dimension_id']] <=> [$right['score'], $right['dimension_id']])
            ->map(fn (array $score) => $this->recommendation($score))
            ->values()
            ->all();

        return [
            'total_score' => $totalScore,
            'maturity_level' => $maturityLevel,
            'dimension_scores' => $dimensionScores,
            'recommendations' => $recommendations,
            'recommendation' => $recommendations[0] ?? null,
            'snapshot' => [
                'algorithm_version' => 2,
                'calculated_at' => now()->toIso8601String(),
                'dimension_weights' => $dimensions->mapWithKeys(
                    fn (Dimension $dimension) => [$dimension->id => [
                        'name' => $dimension->name,
                        'weight' => (float) $dimension->weight,
                    ]]
                )->all(),
                'dimension_scores' => collect($dimensionScores)->map(fn (array $score) => [
                    'name' => $score['dimension_name'],
                    'score' => $score['score'],
                    'weight' => $score['weight'],
                ])->all(),
                'maturity_levels' => $maturityLevels->map(fn (MaturityLevel $level) => [
                    'name' => $level->name,
                    'min_score' => (float) $level->minimum_score,
                    'max_score' => (float) $level->maximum_score,
                ])->all(),
                'questions' => $questionSnapshots,
                'recommendations' => $recommendations,
            ],
        ];
    }

    private function scoreAnswer(string $type, ?bool $isCorrect, ?int $scoreValue, int $questionId): int
    {
        if ($type === 'multiple_choice') {
            if ($isCorrect === null) {
                throw new InvalidScoringConfiguration("Kunci jawaban untuk pertanyaan {$questionId} belum ditentukan.");
            }

            return $isCorrect ? 100 : 0;
        }

        if ($type === 'likert') {
            if (! in_array($scoreValue, [0, 25, 50, 75, 100], true)) {
                throw new InvalidScoringConfiguration("Nilai rubrik untuk pertanyaan {$questionId} harus 0, 25, 50, 75, atau 100.");
            }

            return $scoreValue;
        }

        throw new InvalidScoringConfiguration("Tipe pertanyaan {$type} belum didukung.");
    }

    private function validateMaturityLevels(Collection $levels): void
    {
        if ($levels->isEmpty()) {
            throw new InvalidScoringConfiguration('Rentang tingkat kematangan belum dikonfigurasi.');
        }

        $nextMinimum = 0.0;
        foreach ($levels as $level) {
            $minimum = (float) $level->minimum_score;
            $maximum = (float) $level->maximum_score;
            if ($minimum < 0 || $maximum > 100 || $minimum > $maximum || abs($minimum - $nextMinimum) > 0.001) {
                throw new InvalidScoringConfiguration('Rentang tingkat kematangan harus berurutan dan mencakup skor 0–100.');
            }

            $nextMinimum = round($maximum + 0.01, 2);
        }

        if (abs($nextMinimum - 100.01) > 0.001) {
            throw new InvalidScoringConfiguration('Rentang tingkat kematangan harus mencakup skor hingga 100.');
        }
    }

    private function maturityLevel(float $score, Collection $levels): string
    {
        $level = $levels->first(
            fn (MaturityLevel $level) => $score >= (float) $level->minimum_score
                && $score <= (float) $level->maximum_score
        );

        if (! $level) {
            throw new InvalidScoringConfiguration("Skor {$score} tidak tercakup dalam rentang tingkat kematangan.");
        }

        return $level->name;
    }

    private function recommendation(array $lowestDimension): array
    {
        $configured = Recommendation::query()
            ->where('dimension_id', $lowestDimension['dimension_id'])
            ->where('is_active', true)
            ->where('minimum_score', '<=', $lowestDimension['score'])
            ->where('maximum_score', '>=', $lowestDimension['score'])
            ->orderBy('minimum_score')
            ->first();

        if ($configured) {
            return [
                'dimension_id' => $lowestDimension['dimension_id'],
                'dimension_name' => $lowestDimension['dimension_name'],
                'score' => $lowestDimension['score'],
                'title' => $configured->title,
                'description' => $configured->description,
                'learning_url' => $configured->learning_url,
            ];
        }

        return [
            'dimension_id' => $lowestDimension['dimension_id'],
            'dimension_name' => $lowestDimension['dimension_name'],
            'score' => $lowestDimension['score'],
            'title' => 'Dimensi prioritas: '.$lowestDimension['dimension_name'],
            'description' => 'Dimensi ini memperoleh skor terendah. Gunakan hasil ini untuk menentukan prioritas pembelajaran berikutnya.',
            'learning_url' => null,
        ];
    }
}
