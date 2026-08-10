<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrisonerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('prisoner'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'date_of_birth' => ['sometimes', 'required', 'date', 'before:today'],
            'gender' => ['sometimes', 'required', 'in:male,female'],
            'admission_date' => ['sometimes', 'required', 'date'],
            'expected_release_date' => ['nullable', 'date', 'after:admission_date'],
            'status' => ['sometimes', 'required', 'in:in_custody,released,transferred'],
            'photo_path' => ['nullable', 'string'],
        ];
    }
}
