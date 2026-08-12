<?php

namespace App\Http\Resources;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Visit */
class VisitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_request_id' => $this->visit_request_id,
            'prisoner_id' => $this->prisoner_id,
            'prisoner_name' => $this->whenLoaded('prisoner', fn () => $this->prisoner->fullName()),
            'visitor_id' => $this->visitor_id,
            'visitor_name' => $this->whenLoaded('visitor', fn () => $this->visitor->name),
            'scheduled_at' => $this->scheduled_at->toIso8601String(),
            'status' => $this->status->value,
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'checked_in_by' => $this->whenLoaded('checkedInBy', fn () => $this->checkedInBy?->name),
            'checked_out_at' => $this->checked_out_at?->toIso8601String(),
            'checked_out_by' => $this->whenLoaded('checkedOutBy', fn () => $this->checkedOutBy?->name),
            'notes' => $this->notes,
        ];
    }
}
