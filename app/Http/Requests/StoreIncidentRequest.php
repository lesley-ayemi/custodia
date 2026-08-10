<?php

namespace App\Http\Requests;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Incident::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'prisoner_id' => ['required', 'integer', 'exists:prisoners,id'],
            'type' => ['required', 'in:property_damage,rule_violation,accident,altercation,contraband_found,medical_emergency'],
            'severity' => ['required', 'in:low,medium,high'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'occurred_at' => ['required', 'date'],
        ];
    }
}
