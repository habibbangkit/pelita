<?php

namespace App\Http\Requests;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $question = $this->route('question');
        $assessmentId = $this->input('assessment_id');
        $position = Rule::unique('questions', 'position')
            ->where(fn ($query) => $query->where('assessment_id', $assessmentId));

        if ($question instanceof Question) {
            $position->ignore($question->id);
        }

        return [
            'assessment_id' => [
                'required',
                'integer',
                'exists:assessments,id',
                ...($question instanceof Question ? [Rule::in([$question->assessment_id])] : []),
            ],
            'dimension_id' => ['required', 'integer', Rule::exists('dimensions', 'id')->where('is_active', true)],
            'question_text' => ['required', 'string', 'max:5000'],
            'explanation' => ['nullable', 'string', 'max:4000'],
            'type' => ['required', Rule::in(['multiple_choice', 'likert'])],
            'weight' => ['required', 'numeric', 'decimal:0,2', 'between:0.01,9999.99'],
            'position' => ['required', 'integer', 'min:1', $position],
            'is_required' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'options' => ['required', 'array', 'min:2', 'max:10'],
            'options.*.id' => ['nullable', 'integer', 'distinct', 'exists:question_options,id'],
            'options.*.option_text' => ['required', 'string', 'max:255'],
            'options.*.score_value' => [
                Rule::requiredIf($this->input('type') === 'likert'),
                'nullable',
                'integer',
                Rule::in([0, 25, 50, 75, 100]),
            ],
            'correct_option' => [
                Rule::requiredIf($this->input('type') === 'multiple_choice'),
                Rule::prohibitedIf($this->input('type') === 'likert'),
                'nullable',
                'integer',
                'min:0',
                'max:'.max(0, count($this->input('options', [])) - 1),
            ],
        ];
    }
}
