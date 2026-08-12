<?php

namespace App\Http\Resources;

use App\Models\VisitRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin VisitRequest */
class VisitRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visitor_id' => $this->visitor_id,
            'visitor_name' => $this->whenLoaded('visitor', fn () => $this->visitor->name),
            'prisoner_id' => $this->prisoner_id,
            'prisoner_name' => $this->whenLoaded('prisoner', fn () => $this->prisoner->fullName()),
            'relationship' => $this->relationship,
            'requested_by' => $this->whenLoaded('requestedBy', fn () => $this->requestedBy->name),
            'requested_visit_date' => $this->requested_visit_date->toDateString(),
            'status' => $this->status->value,
            'rejection_reason' => $this->rejection_reason,
            'visit' => $this->relationLoaded('visit') && $this->visit
                ? new VisitResource($this->visit)
                : null,
        ];
    }
}
