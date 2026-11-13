<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', 'not_regex:/[\x00-\x1F\x7F]/'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }
}
