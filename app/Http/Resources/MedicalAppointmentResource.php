<?php

namespace App\Http\Resources;

use App\Models\MedicalAppointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MedicalAppointment */
class MedicalAppointmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'prisoner_id' => $this->prisoner_id,
            'prisoner_name' => $this->whenLoaded('prisoner', fn () => $this->prisoner->fullName()),
            'appointment_type' => $this->appointment_type,
            'provider' => $this->provider,
            'location' => $this->location,
            'scheduled_at' => $this->scheduled_at->toIso8601String(),
            'status' => $this->status->value,
            'notes' => $this->notes,
            'scheduled_by' => $this->whenLoaded('scheduledBy', fn () => $this->scheduledBy->name),
        ];
    }
}
