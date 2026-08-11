<?php

namespace App\Http\Resources;

use App\Models\CourtHearing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CourtHearing */
class CourtHearingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'court_case_id' => $this->court_case_id,
            'case_number' => $this->whenLoaded('courtCase', fn () => $this->courtCase->case_number),
            'prisoner_id' => $this->whenLoaded('courtCase', fn () => $this->courtCase->prisoner_id),
            'prisoner_name' => $this->whenLoaded('courtCase', fn () => $this->courtCase->prisoner->fullName()),
            'type' => $this->type->value,
            'scheduled_at' => $this->scheduled_at->toIso8601String(),
            'location' => $this->location,
            'status' => $this->status->value,
            'outcome' => $this->outcome,
            'notes' => $this->notes,
        ];
    }
}
