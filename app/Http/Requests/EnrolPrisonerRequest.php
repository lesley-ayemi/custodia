<?php

namespace App\Http\Requests;

use App\Models\ProgrammeEnrolment;
use Illuminate\Foundation\Http\FormRequest;

class EnrolPrisonerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ProgrammeEnrolment::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'programme_id' => ['required', 'integer', 'exists:programmes,id'],
            'enrolled_at' => ['required', 'date'],
        ];
    }
}
