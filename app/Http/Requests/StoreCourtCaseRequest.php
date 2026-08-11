<?php

namespace App\Http\Requests;

use App\Models\CourtCase;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourtCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CourtCase::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'court_name' => ['required', 'string', 'max:255'],
            'charge' => ['required', 'string', 'max:255'],
            'legal_representative_id' => ['nullable', 'integer', 'exists:legal_representatives,id'],
            'opened_at' => ['required', 'date'],
        ];
    }
}
