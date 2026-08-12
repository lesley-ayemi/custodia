<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordInitialAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('admission'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notes' => ['required', 'string', 'max:2000'],
        ];
    }
}
