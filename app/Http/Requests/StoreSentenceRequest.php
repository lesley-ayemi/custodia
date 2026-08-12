<?php

namespace App\Http\Requests;

use App\Models\Sentence;
use Illuminate\Foundation\Http\FormRequest;

class StoreSentenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Sentence::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'case_number' => ['required', 'string', 'max:255'],
            'court' => ['required', 'string', 'max:255'],
            'offence' => ['required', 'string', 'max:255'],
            'sentence_start' => ['required', 'date'],
            'sentence_end' => ['nullable', 'date', 'after_or_equal:sentence_start'],
            'sentence_type' => ['required', 'in:custodial,suspended,life'],
            'parole_eligibility_date' => ['nullable', 'date'],
            'legal_status' => ['required', 'in:convicted,on_appeal,discharged'],
        ];
    }
}
