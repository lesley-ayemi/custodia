<?php

namespace App\Http\Resources;

use App\Models\ReleaseReviewStep;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReleaseReviewStep */
class ReleaseReviewStepResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'step' => $this->step->value,
            'completed_by' => $this->whenLoaded('completedBy', fn () => $this->completedBy->name),
            'completed_at' => $this->completed_at->toIso8601String(),
            'notes' => $this->notes,
        ];
    }
}
