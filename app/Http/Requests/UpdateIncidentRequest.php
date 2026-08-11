<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('incident'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'required', 'in:property_damage,rule_violation,accident,altercation,contraband_found,medical_emergency'],
            'severity' => ['sometimes', 'required', 'in:low,medium,high'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:2000'],
            'occurred_at' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'in:reported,under_review,resolved'],
        ];
    }
}
