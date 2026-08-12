<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgrammeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('programme'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('programmes', 'name')->ignore($this->route('programme'))],
            'category' => ['sometimes', 'required', 'in:education,counselling,vocational_training,substance_misuse,employment_training,life_skills,other'],
            'description' => ['nullable', 'string', 'max:2000'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'required', 'in:active,inactive'],
        ];
    }
}
