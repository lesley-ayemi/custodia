<?php

namespace App\Http\Requests;

use App\Models\MedicalAlert;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', MedicalAlert::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:255'],
            'severity' => ['required', 'in:low,medium,high'],
        ];
    }
}
