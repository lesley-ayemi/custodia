<?php

namespace App\Http\Resources;

use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Movement */
class MovementResource extends JsonResource
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
            'from_location' => $this->from_location,
            'to_location' => $this->to_location,
            'reason' => $this->reason,
            'requested_by' => $this->whenLoaded('requestedBy', fn () => $this->requestedBy->name),
            'approved_by' => $this->whenLoaded('approvedBy', fn () => $this->approvedBy?->name),
            'scheduled_at' => $this->scheduled_at->toIso8601String(),
            'departed_at' => $this->departed_at?->toIso8601String(),
            'arrived_at' => $this->arrived_at?->toIso8601String(),
            'returned_at' => $this->returned_at?->toIso8601String(),
            'status' => $this->status->value,
        ];
    }
}
