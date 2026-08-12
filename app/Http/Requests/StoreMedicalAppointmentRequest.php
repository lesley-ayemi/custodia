<?php

namespace App\Http\Requests;

use App\Models\MedicalAppointment;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', MedicalAppointment::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'appointment_type' => ['required', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
