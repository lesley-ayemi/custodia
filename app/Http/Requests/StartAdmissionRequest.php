<?php

namespace App\Http\Requests;

use App\Models\Admission;
use Illuminate\Foundation\Http\FormRequest;

class StartAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Admission::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'expected_release_date' => ['nullable', 'date'],
            'admission_date' => ['required', 'date'],
            'admission_reason' => ['required', 'string', 'max:255'],
        ];
    }
}
