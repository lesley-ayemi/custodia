<?php

namespace App\Http\Resources;

use App\Enums\ReleaseReviewStatus;
use App\Enums\ReleaseStep;
use App\Models\ReleaseReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReleaseReview */
class ReleaseReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $steps = $this->whenLoaded('steps');
        $completedValues = is_iterable($steps) ? collect($steps)->pluck('step')->map(fn (ReleaseStep $step) => $step->value)->all() : [];

        return [
            'id' => $this->id,
            'prisoner_id' => $this->prisoner_id,
            'prisoner_name' => $this->whenLoaded('prisoner', fn () => $this->prisoner->fullName()),
            'initiated_by' => $this->whenLoaded('initiatedBy', fn () => $this->initiatedBy->name),
            'initiated_at' => $this->initiated_at->toIso8601String(),
            'status' => $this->status->value,
            'released_at' => $this->released_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $this->cancellation_reason,
            'next_step' => $this->status === ReleaseReviewStatus::InProgress
                ? collect(ReleaseStep::cases())->first(fn (ReleaseStep $step) => ! in_array($step->value, $completedValues, true))?->value
                : null,
            'has_open_court_cases' => $this->whenLoaded('prisoner', fn () => $this->prisoner->courtCases()->where('status', '!=', 'closed')->exists()),
            'has_unreleased_property' => $this->whenLoaded('prisoner', fn () => $this->prisoner->propertyItems()->whereNull('released_at')->exists()),
            'steps' => ReleaseReviewStepResource::collection($steps),
        ];
    }
}
