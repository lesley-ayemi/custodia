<?php

namespace App\Http\Resources;

use App\Models\CourtCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CourtCase */
class CourtCaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_number' => $this->case_number,
            'prisoner_id' => $this->prisoner_id,
            'prisoner_name' => $this->whenLoaded('prisoner', fn () => $this->prisoner->fullName()),
            'court_name' => $this->court_name,
            'charge' => $this->charge,
            'status' => $this->status->value,
            'opened_at' => $this->opened_at->toDateString(),
            'closed_at' => $this->closed_at?->toDateString(),
            'legal_representative' => $this->relationLoaded('legalRepresentative') && $this->legalRepresentative
                ? new LegalRepresentativeResource($this->legalRepresentative)
                : null,
            'hearings' => CourtHearingResource::collection($this->whenLoaded('hearings')),
        ];
    }
}
