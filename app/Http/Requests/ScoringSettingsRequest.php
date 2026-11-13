<?php

namespace App\Http\Requests;

use App\Models\Dimension;
use App\Models\MaturityLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ScoringSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dimensions' => ['required', 'array'],
            'dimensions.*.weight' => ['required', 'numeric', 'decimal:0,2', 'between:0,100'],
            'dimensions.*.is_active' => ['required', 'boolean'],
            'levels' => ['required', 'array'],
            'levels.*.minimum_score' => ['required', 'numeric', 'decimal:0,2', 'between:0,100'],
            'levels.*.maximum_score' => ['required', 'numeric', 'decimal:0,2', 'between:0,100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $dimensions = Dimension::query()->get(['id']);
            $levels = MaturityLevel::query()->orderBy('position')->get(['id', 'name', 'minimum_score', 'maximum_score', 'position']);
            $submittedDimensions = $this->input('dimensions', []);
            $submittedLevels = $this->input('levels', []);
            $submittedDimensions = is_array($submittedDimensions) ? $submittedDimensions : [];
            $submittedLevels = is_array($submittedLevels) ? $submittedLevels : [];

            if (array_diff($dimensions->modelKeys(), array_map('intval', array_keys($submittedDimensions))) !== []) {
                $validator->errors()->add('dimensions', 'Semua dimensi harus ikut dikirim.');
            }

            $activeWeights = collect($submittedDimensions)
                ->filter(fn ($dimension) => is_array($dimension)
                    && filter_var($dimension['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN))
                ->sum(fn ($dimension) => (float) ($dimension['weight'] ?? 0));
            if (abs($activeWeights - 100) > 0.01) {
                $validator->errors()->add('dimensions', 'Total bobot dimensi aktif harus tepat 100%.');
            }

            if (array_diff($levels->modelKeys(), array_map('intval', array_keys($submittedLevels))) !== []) {
                $validator->errors()->add('levels', 'Semua tingkat kematangan harus ikut dikirim.');

                return;
            }

            $nextMinimum = 0.0;
            foreach ($levels as $level) {
                $values = $submittedLevels[$level->id] ?? $submittedLevels[(string) $level->id] ?? null;
                if (! is_array($values) || ! isset($values['minimum_score'], $values['maximum_score'])) {
                    continue;
                }

                $minimum = (float) $values['minimum_score'];
                $maximum = (float) $values['maximum_score'];
                if ($minimum > $maximum || abs($minimum - $nextMinimum) > 0.001) {
                    $validator->errors()->add('levels', 'Rentang level harus berurutan, tidak tumpang tindih, dan dimulai dari 0.');

                    return;
                }
                $nextMinimum = round($maximum + 0.01, 2);
            }

            if (abs($nextMinimum - 100.01) > 0.001) {
                $validator->errors()->add('levels', 'Rentang level harus berakhir pada 100.');
            }
        });
    }
}
