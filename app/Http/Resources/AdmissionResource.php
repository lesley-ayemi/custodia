<?php

namespace App\Http\Resources;

use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Admission */
class AdmissionResource extends JsonResource
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
            'admitted_by' => $this->whenLoaded('admittedBy', fn () => $this->admittedBy->name),
            'admission_date' => $this->admission_date->toDateString(),
            'admission_reason' => $this->admission_reason,
            'legal_authority_reference' => $this->legal_authority_reference,
            'initial_assessment_notes' => $this->initial_assessment_notes,
            'security_classification' => $this->security_classification?->value,
            'status' => $this->status->value,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'has_property' => $this->whenLoaded('prisoner', fn () => $this->prisoner->propertyItems()->exists()),
            'has_housing' => $this->whenLoaded('prisoner', fn () => $this->prisoner->currentHousing()->exists()),
        ];
    }
}
