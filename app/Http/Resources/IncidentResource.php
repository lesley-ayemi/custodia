<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Incident */
class IncidentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'incident_number' => $this->incident_number,
            'prisoner_id' => $this->prisoner_id,
            'prisoner_number' => $this->prisoner->prisoner_number,
            'prisoner_name' => $this->prisoner->fullName(),
            'officer_name' => $this->officer->name,
            'type' => $this->type->value,
            'severity' => $this->severity->value,
            'location' => $this->location,
            'description' => $this->description,
            'occurred_at' => $this->occurred_at->toIso8601String(),
            'status' => $this->status->value,
            'resolved_by' => $this->whenLoaded('resolvedBy', fn () => $this->resolvedBy?->name),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
        ];
    }
}
