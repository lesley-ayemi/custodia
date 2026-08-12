<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordReleaseStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('recordStep', $this->route('releaseReview'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
